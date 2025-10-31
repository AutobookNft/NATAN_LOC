"""AI Router - Selects and instantiates provider adapters"""
from typing import Dict, Any
from app.services.policy_engine import PolicyEngine
from app.services.providers import (
    OpenAIChatAdapter,
    OpenAIEmbeddingAdapter,
    AnthropicChatAdapter,
    OllamaChatAdapter,
    OllamaEmbeddingAdapter,
    BaseChatAdapter,
    BaseEmbeddingAdapter
)

class AIRouter:
    """Routes AI requests to appropriate provider adapters"""
    
    def __init__(self):
        self.policy_engine = PolicyEngine()
        self._chat_adapters: Dict[str, BaseChatAdapter] = {}
        self._embedding_adapters: Dict[str, BaseEmbeddingAdapter] = {}
    
    def get_chat_adapter(self, context: Dict[str, Any]) -> BaseChatAdapter:
        """Get chat adapter based on context"""
        model = self.policy_engine.select_chat_model(context)
        
        # Cache adapters
        if model not in self._chat_adapters:
            self._chat_adapters[model] = self._create_chat_adapter(model)
        
        return self._chat_adapters[model]
    
    def get_embedding_adapter(self, context: Dict[str, Any]) -> BaseEmbeddingAdapter:
        """Get embedding adapter based on context"""
        model = self.policy_engine.select_embedding_model(context)
        
        # Cache adapters
        if model not in self._embedding_adapters:
            self._embedding_adapters[model] = self._create_embedding_adapter(model)
        
        return self._embedding_adapters[model]
    
    def _create_chat_adapter(self, model: str) -> BaseChatAdapter:
        """Create chat adapter instance for model"""
        if model.startswith("anthropic."):
            return AnthropicChatAdapter(model=model)
        elif model.startswith("openai."):
            model_name = model.replace("openai.", "")
            return OpenAIChatAdapter(model=model_name)
        elif model.startswith("ollama."):
            return OllamaChatAdapter(model=model)
        else:
            raise ValueError(f"Unknown chat model: {model}")
    
    def _create_embedding_adapter(self, model: str) -> BaseEmbeddingAdapter:
        """Create embedding adapter instance for model"""
        if model.startswith("openai."):
            # Remove prefix and use model name as-is (already includes text-embedding-3-small, etc.)
            model_name = model.replace("openai.", "")
            return OpenAIEmbeddingAdapter(model=model_name)
        elif model.startswith("ollama."):
            model_name = model.replace("ollama.", "")
            return OllamaEmbeddingAdapter(model=model_name)
        else:
            raise ValueError(f"Unknown embedding model: {model}")





