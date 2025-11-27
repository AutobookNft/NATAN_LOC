"""
Groq provider adapter - LLaMA 3.1 70B ultra-veloce e economico

Groq offre:
- LLaMA 3.1 70B/8B inference velocissima (chip custom)
- Tier gratuito con rate limiting
- Costi molto bassi per tier a pagamento
- Perfetto per task generativi ad alto volume

Docs: https://console.groq.com/docs/quickstart
"""
import os
import httpx
import logging
from typing import List, Dict, Any
from .base import BaseChatAdapter
from .api_errors import (
    APIError,
    APIErrorType,
    parse_api_error,
    InsufficientFundsError,
    InvalidAPIKeyError,
    RateLimitError
)

logger = logging.getLogger(__name__)


class GroqChatAdapter(BaseChatAdapter):
    """
    Groq adapter per LLaMA 3.1 e altri modelli.
    
    Modelli disponibili (Nov 2025):
    - llama-3.1-70b-versatile: Migliore qualità, 128k context
    - llama-3.1-8b-instant: Più veloce, 128k context
    - llama-3.2-90b-vision-preview: Con visione
    - mixtral-8x7b-32768: Alternativa Mistral
    - gemma2-9b-it: Google Gemma
    """
    
    # Modelli disponibili su Groq
    AVAILABLE_MODELS = {
        "llama-3.1-70b": "llama-3.1-70b-versatile",
        "llama-3.1-8b": "llama-3.1-8b-instant", 
        "llama-3.2-90b": "llama-3.2-90b-vision-preview",
        "mixtral-8x7b": "mixtral-8x7b-32768",
        "gemma2-9b": "gemma2-9b-it",
        # Alias per compatibilità
        "llama-70b": "llama-3.1-70b-versatile",
        "llama-8b": "llama-3.1-8b-instant",
    }
    
    # Default model
    DEFAULT_MODEL = "llama-3.1-70b-versatile"
    
    def __init__(self, model: str = "llama-3.1-70b"):
        self.api_key = os.getenv("GROQ_API_KEY")
        self.base_url = "https://api.groq.com/openai/v1"
        
        # Risolvi alias modello
        self.model = self.AVAILABLE_MODELS.get(model, model)
        
        if not self.model:
            self.model = self.DEFAULT_MODEL
            
        logger.info(f"Groq adapter initialized with model: {self.model}")
    
    async def generate(self, messages: List[Dict[str, str]], **options) -> Dict[str, Any]:
        """
        Genera risposta usando Groq API (compatibile OpenAI).
        
        Args:
            messages: Lista di messaggi [{role, content}]
            **options: max_tokens, temperature, etc.
            
        Returns:
            Dict con content, usage, model, finish_reason
        """
        if not self.api_key:
            raise InvalidAPIKeyError(
                provider="groq",
                message="GROQ_API_KEY environment variable not set. Get your free key at https://console.groq.com"
            )
        
        # Groq usa formato OpenAI-compatibile
        async with httpx.AsyncClient(timeout=120.0) as client:
            try:
                response = await client.post(
                    f"{self.base_url}/chat/completions",
                    headers={
                        "Authorization": f"Bearer {self.api_key}",
                        "Content-Type": "application/json"
                    },
                    json={
                        "model": self.model,
                        "messages": messages,
                        "max_tokens": options.get("max_tokens", 8192),
                        "temperature": options.get("temperature", 0.7),
                        "top_p": options.get("top_p", 1.0),
                        "stream": False
                    }
                )
                
                # Gestione errori HTTP
                if response.status_code != 200:
                    error_text = response.text
                    logger.error(f"Groq API error {response.status_code}: {error_text}")
                    
                    # Parse errore specifico
                    error = parse_api_error(response.status_code, error_text, "groq")
                    raise error
                
                data = response.json()
                
                # Estrai contenuto dalla risposta
                content = data["choices"][0]["message"]["content"]
                
                return {
                    "content": content,
                    "usage": {
                        "input_tokens": data["usage"]["prompt_tokens"],
                        "output_tokens": data["usage"]["completion_tokens"],
                        "total_tokens": data["usage"]["total_tokens"]
                    },
                    "model": data["model"],
                    "finish_reason": data["choices"][0].get("finish_reason", "stop")
                }
                
            except httpx.HTTPStatusError as e:
                logger.error(f"Groq HTTP error: {e.response.status_code} - {e.response.text}")
                error = parse_api_error(e.response.status_code, e.response.text, "groq")
                raise error
                
            except httpx.TimeoutException:
                raise APIError(
                    message="Groq API timeout",
                    error_type=APIErrorType.TIMEOUT,
                    provider="groq"
                )
                
            except Exception as e:
                logger.error(f"Groq unexpected error: {str(e)}")
                raise APIError(
                    message=str(e),
                    error_type=APIErrorType.UNKNOWN,
                    provider="groq"
                )
    
    async def generate_stream(self, messages: List[Dict[str, str]], **options):
        """
        Genera risposta in streaming (per UX real-time).
        
        Yields:
            Chunks di testo man mano che arrivano
        """
        if not self.api_key:
            raise InvalidAPIKeyError(provider="groq")
        
        async with httpx.AsyncClient(timeout=120.0) as client:
            async with client.stream(
                "POST",
                f"{self.base_url}/chat/completions",
                headers={
                    "Authorization": f"Bearer {self.api_key}",
                    "Content-Type": "application/json"
                },
                json={
                    "model": self.model,
                    "messages": messages,
                    "max_tokens": options.get("max_tokens", 8192),
                    "temperature": options.get("temperature", 0.7),
                    "stream": True
                }
            ) as response:
                async for line in response.aiter_lines():
                    if line.startswith("data: "):
                        data = line[6:]
                        if data == "[DONE]":
                            break
                        try:
                            import json
                            chunk = json.loads(data)
                            content = chunk["choices"][0].get("delta", {}).get("content", "")
                            if content:
                                yield content
                        except:
                            continue
    
    def get_model_info(self) -> Dict[str, Any]:
        """Restituisce info sul modello corrente"""
        return {
            "provider": "groq",
            "model": self.model,
            "max_context": 128000 if "70b" in self.model or "8b" in self.model else 32768,
            "supports_streaming": True,
            "supports_vision": "vision" in self.model,
            "pricing": "Free tier available, ~$0.59/1M tokens for 70B"
        }


# Alias per import semplificato
LlamaChatAdapter = GroqChatAdapter
