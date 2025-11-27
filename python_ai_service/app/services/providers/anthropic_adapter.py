"""Anthropic (Claude) provider adapter"""
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

class AnthropicChatAdapter(BaseChatAdapter):
    """Anthropic Claude chat adapter"""
    
    # Fallback chain per discovery dinamico dei model ID
    # Proviamo in ordine: più recente → più stabile
    # Note: Claude 3.5 Sonnet è deprecato, migriamo a Claude Sonnet 4
    MODEL_FALLBACKS = {
        "claude-3-5-sonnet": [
            "claude-sonnet-4-20250514",  # Claude Sonnet 4 (nuovo standard)
            "claude-3-5-sonnet-20241022",  # Deprecato ma ancora disponibile
            "claude-3-5-sonnet-20240620",  # Deprecato ma ancora disponibile
        ],
        "claude-sonnet-4": [
            "claude-sonnet-4-20250514",  # Latest Claude Sonnet 4
        ],
        "claude-3-opus": [
            "claude-3-opus-20240229",
        ],
        "claude-3-sonnet": [
            "claude-3-sonnet-20240229",
        ],
    }
    
    def __init__(self, model: str = "claude-sonnet-3-20240229"):
        self.api_key = os.getenv("ANTHROPIC_API_KEY")
        self.base_url = "https://api.anthropic.com/v1"
        
        # Map model names to base model identifier (senza data)
        self.model_map = {
            "anthropic.sonnet-3.5": "claude-3-5-sonnet",
            "anthropic.opus-3": "claude-3-opus",
            "claude-sonnet-3-20240229": "claude-3-5-sonnet",
        }
        
        # Convert to base model identifier
        base_model = self.model_map.get(model, model)
        
        # If model still contains prefix, remove it
        if base_model.startswith("anthropic."):
            base_model = base_model.replace("anthropic.", "")
        
        # If it's a full model ID with date, extract base name
        if "-202" in base_model or "-2024" in base_model:
            # Extract base name (e.g., "claude-3-5-sonnet-20241022" → "claude-3-5-sonnet")
            parts = base_model.split("-")
            date_idx = next((i for i, p in enumerate(parts) if p.startswith("202")), None)
            if date_idx:
                base_model = "-".join(parts[:date_idx])
        
        # Store base model for dynamic discovery
        self.base_model = base_model
        self.model = None  # Will be discovered on first use
        self._discovered_model = None  # Cache discovered model
    
    async def _discover_model(self) -> str:
        """Discover available model ID by trying fallback chain"""
        if self._discovered_model:
            return self._discovered_model
        
        # Get fallback list for this base model
        fallbacks = self.MODEL_FALLBACKS.get(self.base_model, [self.base_model])
        
        # Try each model ID in fallback chain
        for model_id in fallbacks:
            try:
                # Test with minimal request (just to check if model exists)
                async with httpx.AsyncClient(timeout=10.0) as client:
                    # Make a minimal test request
                    test_response = await client.post(
                        f"{self.base_url}/messages",
                        headers={
                            "x-api-key": self.api_key,
                            "anthropic-version": "2023-06-01",
                            "Content-Type": "application/json"
                        },
                        json={
                            "model": model_id,
                            "max_tokens": 10,
                            "messages": [{"role": "user", "content": "test"}]
                        }
                    )
                    if test_response.status_code == 200:
                        # Model exists and works!
                        self._discovered_model = model_id
                        import logging
                        logger = logging.getLogger(__name__)
                        logger.info(f"✅ Discovered working Anthropic model: {model_id} for base {self.base_model}")
                        return model_id
                    
                    # Controlla errori specifici che non dovremmo ignorare
                    # 400 può essere anche "credit balance too low" su Anthropic
                    if test_response.status_code in [400, 401, 402, 429]:
                        response_text = test_response.text.lower()
                        # Controlla se è errore di credito/billing
                        if any(kw in response_text for kw in ['credit', 'balance', 'billing', 'payment', 'insufficient']):
                            error = parse_api_error(402, test_response.text, "anthropic")
                            raise error
                        # Altri errori 400 potrebbero essere modello non valido, proviamo il prossimo
                        if test_response.status_code == 400:
                            logger.warning(f"Model {model_id} returned 400: {test_response.text[:200]}")
                            continue
                        # 401, 402, 429 sono errori critici
                        error = parse_api_error(
                            test_response.status_code, 
                            test_response.text, 
                            "anthropic"
                        )
                        raise error
                        
            except (InsufficientFundsError, InvalidAPIKeyError, RateLimitError) as api_err:
                # Errori critici che non dipendono dal modello - propagali
                raise api_err
            except httpx.HTTPStatusError as e:
                if e.response.status_code == 404:
                    # Model not found, try next in fallback chain
                    continue
                # Check for billing/auth errors
                if e.response.status_code in [401, 402, 429]:
                    error = parse_api_error(
                        e.response.status_code, 
                        e.response.text, 
                        "anthropic"
                    )
                    raise error
                # Other error, re-raise
                raise
            except APIError:
                # Already parsed API error, propagate
                raise
            except Exception as e:
                # Network/timeout errors, log and try next
                logger.warning(f"Error testing model {model_id}: {str(e)[:100]}")
                continue
        
        # No model found in fallback chain - potrebbe essere problema API key o billing
        # Trasforma in APIError per messaggio user-friendly
        raise APIError(
            message=f"Anthropic API: No available model found for '{self.base_model}'. Tried: {fallbacks}. Check your API key and billing status.",
            error_type=APIErrorType.MODEL_NOT_AVAILABLE,
            provider="anthropic",
            status_code=400,
            user_message=(
                "⚠️ **Servizio AI temporaneamente non disponibile**\n\n"
                "Non è stato possibile connettersi al servizio di intelligenza artificiale.\n\n"
                "**Possibili cause:**\n"
                "- Credito API esaurito\n"
                "- Chiave API non valida o scaduta\n"
                "- Servizio temporaneamente non raggiungibile\n\n"
                "**Cosa fare?**\n"
                "Contatta l'amministratore di sistema per verificare lo stato del servizio."
            )
        )
    
    async def generate(self, messages: List[Dict[str, str]], **options) -> Dict[str, Any]:
        """Generate chat response using Anthropic API"""
        if not self.api_key:
            raise ValueError("ANTHROPIC_API_KEY environment variable not set")
        
        # Discover model ID if not already discovered
        if not self.model:
            self.model = await self._discover_model()
        
        # Convert messages to Anthropic format
        anthropic_messages = []
        system_message = None
        
        for msg in messages:
            if msg["role"] == "system":
                system_message = msg["content"]
            else:
                # Anthropic uses "user" and "assistant" roles
                role = "user" if msg["role"] == "user" else "assistant"
                anthropic_messages.append({
                    "role": role,
                    "content": msg["content"]
                })
        
        async with httpx.AsyncClient(timeout=60.0) as client:
            try:
                response = await client.post(
                    f"{self.base_url}/messages",
                    headers={
                        "x-api-key": self.api_key,
                        "anthropic-version": "2023-06-01",
                        "Content-Type": "application/json"
                    },
                json={
                    "model": self.model,
                    "max_tokens": options.get("max_tokens", 8192),
                    "temperature": options.get("temperature", 0.7),
                    "messages": anthropic_messages,
                    **({"system": system_message} if system_message else {})
                }
                )
                response.raise_for_status()
                data = response.json()
            except httpx.HTTPStatusError as e:
                # Log dettagli errore per debug
                logger.error(f"Anthropic API error {e.response.status_code}: {e.response.text}")
                
                # Gestisci errori critici (billing, auth, rate limit)
                if e.response.status_code in [401, 402, 429]:
                    error = parse_api_error(
                        e.response.status_code, 
                        e.response.text, 
                        "anthropic"
                    )
                    raise error
                
                if e.response.status_code == 404:
                    # Model might have been deprecated, re-discover
                    self._discovered_model = None
                    self.model = await self._discover_model()
                    # Retry with discovered model
                    response = await client.post(
                        f"{self.base_url}/messages",
                        headers={
                            "x-api-key": self.api_key,
                            "anthropic-version": "2023-06-01",
                            "Content-Type": "application/json"
                        },
                        json={
                            "model": self.model,
                            "max_tokens": options.get("max_tokens", 8192),
                            "temperature": options.get("temperature", 0.7),
                            "messages": anthropic_messages,
                            **({"system": system_message} if system_message else {})
                        }
                    )
                    response.raise_for_status()
                    data = response.json()
                else:
                    # Errore generico - prova a parsarlo
                    error = parse_api_error(
                        e.response.status_code, 
                        e.response.text, 
                        "anthropic"
                    )
                    raise error
            
            content_text = ""
            if isinstance(data["content"], list):
                content_text = "".join(
                    item.get("text", "") for item in data["content"] if item.get("type") == "text"
                )
            else:
                content_text = str(data["content"])
            
            return {
                "content": content_text,
                "usage": {
                    "input_tokens": data["usage"]["input_tokens"],
                    "output_tokens": data["usage"]["output_tokens"]
                },
                "model": data["model"],
                "finish_reason": data.get("stop_reason", "stop")
            }





