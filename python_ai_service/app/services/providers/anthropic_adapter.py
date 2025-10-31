"""Anthropic (Claude) provider adapter"""
import os
import httpx
from typing import List, Dict, Any
from .base import BaseChatAdapter

class AnthropicChatAdapter(BaseChatAdapter):
    """Anthropic Claude chat adapter"""
    
    def __init__(self, model: str = "claude-sonnet-3-20240229"):
        self.api_key = os.getenv("ANTHROPIC_API_KEY")
        self.base_url = "https://api.anthropic.com/v1"
        
        # Map model names to Anthropic model IDs
        # Updated to use correct Claude 3.5 Sonnet model ID
        self.model_map = {
            "anthropic.sonnet-3.5": "claude-3-5-sonnet-20241022",
            "anthropic.opus-3": "claude-3-opus-20240229",
            "claude-sonnet-3-20240229": "claude-3-5-sonnet-20241022",  # Legacy mapping
        }
        self.model = self.model_map.get(model, model)
    
    async def generate(self, messages: List[Dict[str, str]], **options) -> Dict[str, Any]:
        """Generate chat response using Anthropic API"""
        if not self.api_key:
            raise ValueError("ANTHROPIC_API_KEY environment variable not set")
        
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





