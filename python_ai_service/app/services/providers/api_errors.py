"""
Custom exceptions per errori API LLM providers.
Permette gestione granulare e messaggi user-friendly.
"""

from enum import Enum
from typing import Optional


class APIErrorType(Enum):
    """Tipi di errore API riconosciuti"""
    INSUFFICIENT_FUNDS = "insufficient_funds"
    INVALID_API_KEY = "invalid_api_key"
    RATE_LIMITED = "rate_limited"
    MODEL_NOT_AVAILABLE = "model_not_available"
    QUOTA_EXCEEDED = "quota_exceeded"
    SERVICE_UNAVAILABLE = "service_unavailable"
    TIMEOUT = "timeout"
    UNKNOWN = "unknown"


class APIError(Exception):
    """Eccezione base per errori API LLM"""
    
    def __init__(
        self, 
        message: str, 
        error_type: APIErrorType = APIErrorType.UNKNOWN,
        provider: str = "unknown",
        status_code: int = None,
        user_message: str = None,
        retry_after: int = None
    ):
        self.message = message
        self.error_type = error_type
        self.provider = provider
        self.status_code = status_code
        self.retry_after = retry_after
        
        # Messaggio user-friendly
        self.user_message = user_message or self._get_default_user_message()
        
        super().__init__(self.message)
    
    def _get_default_user_message(self) -> str:
        """Genera messaggio user-friendly basato sul tipo di errore"""
        messages = {
            APIErrorType.INSUFFICIENT_FUNDS: (
                "⚠️ **Servizio AI temporaneamente non disponibile**\n\n"
                "Il credito API per il servizio di intelligenza artificiale è esaurito.\n\n"
                "**Cosa significa?**\n"
                "Il sistema NATAN utilizza servizi AI esterni (Claude, GPT) che richiedono credito prepagato.\n\n"
                "**Cosa puoi fare?**\n"
                "- Attendi che l'amministratore ricarichi il credito\n"
                "- Contatta il supporto tecnico per informazioni\n"
                "- Prova più tardi\n\n"
                "*I tuoi dati sono al sicuro. Il problema è solo nel servizio di generazione testi.*"
            ),
            APIErrorType.INVALID_API_KEY: (
                "⚠️ **Configurazione API non valida**\n\n"
                "La chiave API per il servizio AI non è configurata correttamente.\n\n"
                "**Cosa fare?**\n"
                "Contatta l'amministratore di sistema per verificare la configurazione."
            ),
            APIErrorType.RATE_LIMITED: (
                "⏳ **Troppe richieste**\n\n"
                "Il sistema ha raggiunto il limite di richieste al minuto.\n\n"
                "**Cosa fare?**\n"
                "Attendi qualche secondo e riprova."
            ),
            APIErrorType.MODEL_NOT_AVAILABLE: (
                "🔧 **Modello AI non disponibile**\n\n"
                "Il modello AI richiesto non è al momento disponibile.\n\n"
                "**Cosa fare?**\n"
                "Il sistema proverà automaticamente con modelli alternativi. Riprova tra poco."
            ),
            APIErrorType.QUOTA_EXCEEDED: (
                "📊 **Quota giornaliera superata**\n\n"
                "È stato raggiunto il limite di utilizzo giornaliero del servizio AI.\n\n"
                "**Cosa fare?**\n"
                "Riprova domani oppure contatta l'amministratore per aumentare la quota."
            ),
            APIErrorType.SERVICE_UNAVAILABLE: (
                "🔌 **Servizio AI temporaneamente non raggiungibile**\n\n"
                "Il provider AI esterno non risponde.\n\n"
                "**Cosa fare?**\n"
                "Attendi qualche minuto e riprova. Se il problema persiste, contatta il supporto."
            ),
            APIErrorType.TIMEOUT: (
                "⏱️ **Timeout della richiesta**\n\n"
                "La risposta dal servizio AI sta richiedendo troppo tempo.\n\n"
                "**Cosa fare?**\n"
                "Prova con una domanda più breve o specifica."
            ),
            APIErrorType.UNKNOWN: (
                "❌ **Errore imprevisto**\n\n"
                "Si è verificato un errore durante l'elaborazione.\n\n"
                "**Cosa fare?**\n"
                "Riprova. Se il problema persiste, contatta il supporto tecnico."
            )
        }
        return messages.get(self.error_type, messages[APIErrorType.UNKNOWN])


class InsufficientFundsError(APIError):
    """Errore specifico per credito API esaurito"""
    def __init__(self, provider: str, message: str = None):
        super().__init__(
            message=message or f"Insufficient funds/credits on {provider} API",
            error_type=APIErrorType.INSUFFICIENT_FUNDS,
            provider=provider,
            status_code=402
        )


class InvalidAPIKeyError(APIError):
    """Errore specifico per API key non valida"""
    def __init__(self, provider: str, message: str = None):
        super().__init__(
            message=message or f"Invalid API key for {provider}",
            error_type=APIErrorType.INVALID_API_KEY,
            provider=provider,
            status_code=401
        )


class RateLimitError(APIError):
    """Errore specifico per rate limiting"""
    def __init__(self, provider: str, retry_after: int = None, message: str = None):
        super().__init__(
            message=message or f"Rate limited by {provider}",
            error_type=APIErrorType.RATE_LIMITED,
            provider=provider,
            status_code=429,
            retry_after=retry_after
        )


class QuotaExceededError(APIError):
    """Errore specifico per quota superata"""
    def __init__(self, provider: str, message: str = None):
        super().__init__(
            message=message or f"Quota exceeded on {provider}",
            error_type=APIErrorType.QUOTA_EXCEEDED,
            provider=provider,
            status_code=429
        )


def parse_api_error(
    status_code: int, 
    response_text: str, 
    provider: str
) -> APIError:
    """
    Analizza risposta errore API e restituisce eccezione appropriata.
    
    Args:
        status_code: HTTP status code
        response_text: Corpo della risposta errore
        provider: Nome del provider (anthropic, openai, etc.)
        
    Returns:
        APIError appropriata al tipo di errore
    """
    response_lower = response_text.lower()
    
    # Pattern comuni per diversi tipi di errore
    if status_code == 401:
        if "invalid" in response_lower or "unauthorized" in response_lower:
            return InvalidAPIKeyError(provider, response_text)
    
    if status_code == 402 or "insufficient" in response_lower or "credit" in response_lower or "funds" in response_lower:
        return InsufficientFundsError(provider, response_text)
    
    if status_code == 429:
        if "quota" in response_lower:
            return QuotaExceededError(provider, response_text)
        return RateLimitError(provider, message=response_text)
    
    if status_code == 503 or status_code == 502:
        return APIError(
            message=response_text,
            error_type=APIErrorType.SERVICE_UNAVAILABLE,
            provider=provider,
            status_code=status_code
        )
    
    if status_code == 404 and "model" in response_lower:
        return APIError(
            message=response_text,
            error_type=APIErrorType.MODEL_NOT_AVAILABLE,
            provider=provider,
            status_code=status_code
        )
    
    # Errore generico
    return APIError(
        message=response_text,
        error_type=APIErrorType.UNKNOWN,
        provider=provider,
        status_code=status_code
    )
