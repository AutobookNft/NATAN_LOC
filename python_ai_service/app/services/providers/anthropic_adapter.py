"""Anthropic (Claude) provider adapter"""
import os
import httpx
from typing import List, Dict, Any
from .base import BaseChatAdapter

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
            except httpx.HTTPStatusError as e:
                if e.response.status_code == 404:
                    # Model not found, try next in fallback chain
                    continue
                # Other error, re-raise
                raise
            except Exception:
                # Network/timeout errors, try next
                continue
        
        # No model found in fallback chain
        raise ValueError(
            f"Anthropic API: No available model found for '{self.base_model}'. "
            f"Tried: {fallbacks}. Check your API key and model availability."
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
                    raise
            
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





