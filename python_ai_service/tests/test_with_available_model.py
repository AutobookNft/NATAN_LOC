"""
Test Helper - Get available model automatically
"""

import asyncio
from app.services.ai_router import AIRouter


async def get_available_model():
    """Get available Anthropic model"""
    try:
        ai_router = AIRouter()
        context = {"tenant_id": 1, "task_class": "USE"}
        adapter = ai_router.get_chat_adapter(context)
        
        # If adapter has discovery, use it
        if hasattr(adapter, '_discover_model'):
            try:
                model = await adapter._discover_model()
                return model
            except:
                pass
        
        # Check what base model is configured
        if hasattr(adapter, 'base_model'):
            return adapter.base_model
        
        # Fallback
        return "anthropic.sonnet-4"
    except:
        return "anthropic.sonnet-4"


if __name__ == "__main__":
    print(asyncio.run(get_available_model()))






