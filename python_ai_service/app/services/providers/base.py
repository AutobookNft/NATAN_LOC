"""Base classes for AI provider adapters"""
from abc import ABC, abstractmethod
from typing import List, Dict, Any

class BaseChatAdapter(ABC):
    """Base class for chat model adapters"""
    
    @abstractmethod
    async def generate(self, messages: List[Dict[str, str]], **options) -> Dict[str, Any]:
        """
        Generate chat response
        
        Args:
            messages: List of message dicts with 'role' and 'content'
            **options: Additional model options (temperature, max_tokens, etc.)
        
        Returns:
            Dict with 'content', 'usage', 'model', 'finish_reason'
        """
        pass

class BaseEmbeddingAdapter(ABC):
    """Base class for embedding model adapters"""
    
    @abstractmethod
    async def embed(self, text: str, **options) -> Dict[str, Any]:
        """
        Generate text embedding
        
        Args:
            text: Input text to embed
            **options: Additional model options
        
        Returns:
            Dict with 'embedding' (list of floats), 'dimensions', 'model', 'tokens'
        """
        pass




















