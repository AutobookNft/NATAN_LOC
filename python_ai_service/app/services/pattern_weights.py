"""
Pattern Weights - Confidence scoring basato su specificità pattern

Pesi di confidenza per pattern di classificazione:
- HIGH (0.95): Pattern molto specifici (nome, età, nascita, morte, coordinate)
- MEDIUM-HIGH (0.85): Pattern specifici ma comuni (luogo, professione, preferenze forti)
- MEDIUM (0.70): Pattern moderatamente specifici (opinioni, esperienze)
- MEDIUM-LOW (0.55): Pattern generici (interesse generico, domande aperte)
- LOW (0.40): Pattern molto generici (saluti, affermazioni vaghe)

Sistema a cascata: se un pattern HIGH matcha, ritorna subito 0.95
Se nessun HIGH matcha, cerca MEDIUM-HIGH, etc.
"""

from typing import Dict, List, Tuple
from enum import Enum


class ConfidenceLevel(float, Enum):
    """Livelli di confidenza per pattern matching"""
    HIGH = 0.95          # Pattern molto specifici
    MEDIUM_HIGH = 0.85   # Pattern specifici
    MEDIUM = 0.70        # Pattern moderatamente specifici
    MEDIUM_LOW = 0.55    # Pattern generici
    LOW = 0.40           # Pattern molto generici


# PERSONAL QUERY PATTERNS - Categorizzati per confidenza
PERSONAL_PATTERN_WEIGHTS = {
    # HIGH CONFIDENCE - Informazioni identificative uniche
    ConfidenceLevel.HIGH: [
        # Nome/Cognome
        "mi chiamo", "il mio nome è", "sono ", "mi presento",
        "chiamami", "puoi chiamarmi", "mi chiami",
        
        # Nascita/Morte (dati biografici critici)
        "sono nato", "sono nata", "data di nascita", "giorno di nascita",
        "anno di nascita", "quando sono nato", "dove sono nato",
        "luogo di nascita", "città natale", "paese natale",
        
        # Età esatta
        "ho ", "anni", "compio", "compirò", "la mia età",
        
        # Coordinate/Indirizzi
        "il mio indirizzo", "abito in via", "vivo in via",
        "il mio numero civico", "la mia email", "il mio telefono",
    ],
    
    # MEDIUM-HIGH CONFIDENCE - Informazioni personali importanti
    ConfidenceLevel.MEDIUM_HIGH: [
        # Residenza/Domicilio
        "vivo a", "abito a", "risiedo a", "dimoro a",
        "la mia città", "il mio paese", "la mia regione",
        "vengo da", "provengo da", "sono di",
        
        # Professione/Studio
        "lavoro come", "sono un", "faccio il", "la mia professione",
        "il mio lavoro", "occupazione", "mestiere",
        "studio", "frequento", "università", "scuola",
        
        # Famiglia stretta
        "mia moglie", "mio marito", "mia figlia", "mio figlio",
        "miei genitori", "mio padre", "mia madre",
        "mio fratello", "mia sorella",
        
        # Salute critica
        "sono allergico", "allergia a", "intolleranza a",
        "malattia cronica", "patologia", "disabilità",
    ],
    
    # MEDIUM CONFIDENCE - Preferenze forti e esperienze
    ConfidenceLevel.MEDIUM: [
        # Lingue parlate
        "parlo", "conosco la lingua", "madrelingua",
        "so parlare", "fluente in",
        
        # Hobby/Passioni
        "la mia passione", "il mio hobby", "amo fare",
        "dedico tempo a", "pratico", "suono",
        
        # Esperienze importanti
        "ho vissuto a", "sono stato a", "ho visitato",
        "ho lavorato per", "esperienza di",
        
        # Relazioni
        "il mio migliore amico", "la mia migliore amica",
        "partner", "fidanzato", "fidanzata",
        
        # Veicoli/Beni
        "la mia macchina", "la mia auto", "il mio veicolo",
        "casa di proprietà", "appartamento",
    ],
    
    # MEDIUM-LOW CONFIDENCE - Preferenze generiche
    ConfidenceLevel.MEDIUM_LOW: [
        # Gusti generici
        "mi piace", "preferisco", "amo", "adoro",
        "apprezzo", "gradisco", "prediligo",
        
        # Opinioni
        "penso che", "credo che", "secondo me",
        "la mia opinione", "ritengo che",
        
        # Interessi vaghi
        "mi interessa", "sono interessato a",
        "mi incuriosisce", "trovo interessante",
        
        # Esperienze minori
        "ho provato", "ho visto", "ho sentito",
        "conosco", "so che",
    ],
    
    # LOW CONFIDENCE - Pattern troppo generici
    ConfidenceLevel.LOW: [
        # Troppo vaghi
        "qualcosa su", "parlami di me", "cosa sai di me",
        "dimmi qualcosa", "raccontami",
        
        # Domande generiche
        "chi sono", "cosa sono", "dimmi chi",
        
        # Affermazioni vaghe
        "ricorda che", "tieni presente", "sappi che",
    ],
}


# CONVERSATIONAL PATTERN WEIGHTS
CONVERSATIONAL_PATTERN_WEIGHTS = {
    ConfidenceLevel.HIGH: [
        # Saluti formali/informali chiari
        "ciao", "salve", "buongiorno", "buonasera", "buonanotte",
        "hey", "ehi", "bella", "come va", "come stai",
    ],
    
    ConfidenceLevel.MEDIUM_HIGH: [
        # Domande sull'AI
        "chi sei", "cosa sei", "come ti chiami", "sei un", "sei una",
        "cosa puoi fare", "come funzioni", "che tipo di",
    ],
    
    ConfidenceLevel.MEDIUM: [
        # Small talk
        "che tempo fa", "che ore sono", "che giorno è",
        "raccontami una barzelletta", "dimmi qualcosa di divertente",
    ],
    
    ConfidenceLevel.MEDIUM_LOW: [
        # Richieste vaghe
        "dimmi", "raccontami", "parlami", "spiegami",
    ],
}


# GENERATIVE PATTERN WEIGHTS
GENERATIVE_PATTERN_WEIGHTS = {
    ConfidenceLevel.HIGH: [
        # Richieste creative specifiche
        "scrivi un", "crea un", "genera un", "componi un",
        "inventa un", "elabora un", "redigi un",
    ],
    
    ConfidenceLevel.MEDIUM_HIGH: [
        # Richieste creative generiche
        "dammi un'idea", "suggerisci un", "proponi un",
        "immagina un", "descrivi un ipotetico",
    ],
    
    ConfidenceLevel.MEDIUM: [
        # Brainstorming
        "come potrei", "cosa succederebbe se", "e se",
        "ipotizziamo che", "supponiamo che",
    ],
}


# FACT CHECK PATTERN WEIGHTS
FACT_CHECK_PATTERN_WEIGHTS = {
    ConfidenceLevel.HIGH: [
        # Verifiche precise
        "è vero che", "conferma che", "verifica se",
        "dimostratemi che", "prova che",
    ],
    
    ConfidenceLevel.MEDIUM_HIGH: [
        # Domande fattuali specifiche
        "quanti sono", "quanto costa", "qual è il numero",
        "quando è stato", "dove si trova esattamente",
    ],
    
    ConfidenceLevel.MEDIUM: [
        # Domande fattuali generiche
        "cosa", "dove", "quando", "chi", "come",
        "perché", "quale", "quanti", "quante",
    ],
}


def get_pattern_confidence(pattern_text: str, pattern_category: str) -> float:
    """
    Determina la confidenza di un pattern basandosi sulla sua specificità
    
    Args:
        pattern_text: Testo del pattern matchato
        pattern_category: Categoria del pattern (PERSONAL, CONVERSATIONAL, etc.)
    
    Returns:
        float: Livello di confidenza (0.40 - 0.95)
    """
    # Mapping categorie → weights
    weights_map = {
        'PERSONAL': PERSONAL_PATTERN_WEIGHTS,
        'CONVERSATIONAL': CONVERSATIONAL_PATTERN_WEIGHTS,
        'GENERATIVE': GENERATIVE_PATTERN_WEIGHTS,
        'FACT_CHECK': FACT_CHECK_PATTERN_WEIGHTS,
    }
    
    # Recupera weights per categoria
    category_weights = weights_map.get(pattern_category)
    if not category_weights:
        return ConfidenceLevel.MEDIUM  # Default per categorie senza weights
    
    # Cerca pattern in ordine di confidenza (HIGH → LOW)
    pattern_lower = pattern_text.lower().strip()
    
    for confidence_level in sorted(category_weights.keys(), reverse=True):
        patterns = category_weights[confidence_level]
        if any(p in pattern_lower for p in patterns):
            return float(confidence_level)
    
    # Default se nessun pattern matcha
    return ConfidenceLevel.MEDIUM_LOW


def get_weighted_confidence(matched_patterns: List[str], category: str) -> float:
    """
    Calcola confidenza aggregata da multipli pattern matchati
    
    Se matchano pattern di diverse confidenze, prende la più alta
    
    Args:
        matched_patterns: Lista di pattern che hanno matchato
        category: Categoria dei pattern
    
    Returns:
        float: Confidenza aggregata (max tra tutti i pattern)
    """
    if not matched_patterns:
        return ConfidenceLevel.MEDIUM_LOW
    
    confidences = [
        get_pattern_confidence(pattern, category)
        for pattern in matched_patterns
    ]
    
    # Ritorna confidenza massima tra tutti i pattern matchati
    return max(confidences)
