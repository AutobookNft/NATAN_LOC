"""Policy Engine for AI provider selection"""
from typing import Dict, Any, Optional
from app.config import POLICIES

class PolicyEngine:
    """Selects AI provider based on context and policies"""
    
    def __init__(self):
        self.policies = POLICIES.get("policies", [])
        self.fallbacks = POLICIES.get("fallbacks", {})
        self.provider_configs = POLICIES.get("providers", {})
    
    def select_chat_model(self, context: Dict[str, Any]) -> str:
        """
        Select chat model based on context
        
        Args:
            context: Dict with tenant_id, persona, task_class, intent, etc.
        
        Returns:
            Model identifier string (e.g., "anthropic.sonnet-3.5")
        """
        # Try to match policies
        for policy in self.policies:
            match = policy.get("match", {})
            if self._matches(context, match):
                return policy["use"]["chat"]
        
        # Default fallback
        fallback_chain = self.fallbacks.get("chat", [])
        return fallback_chain[0] if fallback_chain else "anthropic.sonnet-3.5"
    
    def select_embedding_model(self, context: Dict[str, Any]) -> str:
        """
        Select embedding model based on context
        
        Args:
            context: Dict with tenant_id, persona, task_class, etc.
        
        Returns:
            Model identifier string (e.g., "openai.text-embedding-3-small")
        """
        # Try to match policies
        for policy in self.policies:
            match = policy.get("match", {})
            if self._matches(context, match):
                return policy["use"]["embed"]
        
        # Default fallback
        fallback_chain = self.fallbacks.get("embed", [])
        return fallback_chain[0] if fallback_chain else "openai.text-embedding-3-small"
    
    def get_provider_config(self, provider: str, model: str) -> Dict[str, Any]:
        """
        Get provider configuration for a model
        
        Args:
            provider: Provider name (e.g., "anthropic", "openai")
            model: Model identifier
        
        Returns:
            Configuration dict
        """
        provider_config = self.provider_configs.get(provider, {})
        model_key = model.split(".")[-1] if "." in model else model
        return provider_config.get("models", {}).get(model_key, {})
    
    def _matches(self, context: Dict[str, Any], match: Dict[str, Any]) -> bool:
        """Check if context matches policy match criteria"""
        for key, value in match.items():
            context_value = context.get(key)
            
            if context_value is None:
                return False
            
            # Handle list values (OR matching)
            if isinstance(value, list):
                if context_value not in value:
                    return False
            # Handle exact match
            elif context_value != value:
                return False
        
        return True







