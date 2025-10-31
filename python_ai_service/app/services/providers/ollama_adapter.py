"""Ollama provider adapters (local models)"""
import os
import httpx
from typing import List, Dict, Any
from .base import BaseChatAdapter, BaseEmbeddingAdapter

class OllamaChatAdapter(BaseChatAdapter):
    """Ollama local chat adapter"""
    
    def __init__(self, model: str = "llama3:70b"):
        self.base_url = os.getenv("OLLAMA_BASE_URL", "http://localhost:11434")
        # Map model names to Ollama model tags
        self.model_map = {
            "ollama.llama3-70b": "llama3:70b",
            "ollama.llama3-8b": "llama3:8b",
        }
        self.model = self.model_map.get(model, model)
    
    async def generate(self, messages: List[Dict[str, str]], **options) -> Dict[str, Any]:
        """Generate chat response using Ollama API"""
        # Convert messages format for Ollama
        prompt = ""
        for msg in messages:
            role_prefix = "Human" if msg["role"] == "user" else "Assistant"
            prompt += f"{role_prefix}: {msg['content']}\n\n"
        prompt += "Assistant:"
        
        async with httpx.AsyncClient(timeout=120.0) as client:
            response = await client.post(
                f"{self.base_url}/api/generate",
                json={
                    "model": self.model,
                    "prompt": prompt,
                    "stream": False,
                    "options": {
                        "temperature": options.get("temperature", 0.7),
                        "num_predict": options.get("max_tokens", 2048),
                    }
                }
            )
            response.raise_for_status()
            data = response.json()
            
            return {
                "content": data["response"],
                "usage": {
                    "input_tokens": data.get("prompt_eval_count", 0),
                    "output_tokens": data.get("eval_count", 0)
                },
                "model": self.model,
                "finish_reason": "stop"
            }

class OllamaEmbeddingAdapter(BaseEmbeddingAdapter):
    """Ollama local embedding adapter"""
    
    def __init__(self, model: str = "nomic-embed"):
        self.base_url = os.getenv("OLLAMA_BASE_URL", "http://localhost:11434")
        self.model = model
        self.dimensions = 768  # nomic-embed default
    
    async def embed(self, text: str, **options) -> Dict[str, Any]:
        """Generate embedding using Ollama API"""
        async with httpx.AsyncClient(timeout=60.0) as client:
            response = await client.post(
                f"{self.base_url}/api/embeddings",
                json={
                    "model": self.model,
                    "prompt": text
                }
            )
            response.raise_for_status()
            data = response.json()
            
            return {
                "embedding": data["embedding"],
                "dimensions": len(data["embedding"]),
                "model": self.model,
                "tokens": len(text.split())  # Approximate
            }


