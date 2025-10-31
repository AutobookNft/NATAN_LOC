"""OpenAI provider adapters"""
import os
import httpx
from typing import List, Dict, Any
from pathlib import Path
from dotenv import load_dotenv
from .base import BaseChatAdapter, BaseEmbeddingAdapter

# Load .env to ensure API keys are available
env_path = Path(__file__).parent.parent.parent.parent / '.env'
load_dotenv(dotenv_path=env_path, override=True)

class OpenAIChatAdapter(BaseChatAdapter):
    """OpenAI GPT chat adapter"""
    
    def __init__(self, model: str = "gpt-4.1"):
        self.api_key = os.getenv("OPENAI_API_KEY")
        self.base_url = "https://api.openai.com/v1"
        self.model = model
    
    async def generate(self, messages: List[Dict[str, str]], **options) -> Dict[str, Any]:
        """Generate chat response using OpenAI API"""
        if not self.api_key:
            raise ValueError("OPENAI_API_KEY environment variable not set")
        
        async with httpx.AsyncClient(timeout=60.0) as client:
            response = await client.post(
                f"{self.base_url}/chat/completions",
                headers={
                    "Authorization": f"Bearer {self.api_key}",
                    "Content-Type": "application/json"
                },
                json={
                    "model": self.model,
                    "messages": messages,
                    "temperature": options.get("temperature", 0.7),
                    "max_tokens": options.get("max_tokens", 4096),
                }
            )
            response.raise_for_status()
            data = response.json()
            
            return {
                "content": data["choices"][0]["message"]["content"],
                "usage": data["usage"],
                "model": data["model"],
                "finish_reason": data["choices"][0]["finish_reason"]
            }

class OpenAIEmbeddingAdapter(BaseEmbeddingAdapter):
    """OpenAI embedding adapter"""
    
    def __init__(self, model: str = "text-embedding-3-small"):
        self.api_key = os.getenv("OPENAI_API_KEY")
        self.base_url = "https://api.openai.com/v1"
        self.model = model
        # Default dimensions based on model
        self.dimensions = {
            "text-embedding-3-large": 3072,
            "text-embedding-3-small": 1536
        }.get(model, 1536)
    
    async def embed(self, text: str, **options) -> Dict[str, Any]:
        """Generate embedding using OpenAI API"""
        if not self.api_key:
            raise ValueError("OPENAI_API_KEY environment variable not set")
        
        async with httpx.AsyncClient(timeout=60.0) as client:
            response = await client.post(
                f"{self.base_url}/embeddings",
                headers={
                    "Authorization": f"Bearer {self.api_key}",
                    "Content-Type": "application/json"
                },
                json={
                    "model": self.model,
                    "input": text,
                    "dimensions": options.get("dimensions", self.dimensions)
                }
            )
            response.raise_for_status()
            data = response.json()
            
            return {
                "embedding": data["data"][0]["embedding"],
                "dimensions": len(data["data"][0]["embedding"]),
                "model": data["model"],
                "tokens": data["usage"]["total_tokens"]
            }





