"""
Conversational Response System
Sistema logico per gestire migliaia di domande conversazionali con risposte variate
"""

import re
import random
from typing import Dict, List, Tuple, Optional
from datetime import datetime


class ConversationalResponseSystem:
    """
    Sistema di risposte conversazionali basato su pattern matching avanzato
    e template con variabili per generare migliaia di risposte diverse
    """
    
    # Categorie principali di domande conversazionali
    CATEGORIES = {
        "greeting": {
            "patterns": [
                r"^(ciao|salve|buongiorno|buonasera|buon pomeriggio|hey|ehi)",
                r"(ciao|salve|buongiorno|buonasera)",
                r"^(hi|hello|good morning|good evening|hey there)"
            ],
            "templates": [
                "{greeting}! {intro} {how_can_help}",
                "{greeting}! {time_based_greeting} {intro} {offer_help}",
                "{greeting}! Sono {name}. {intro} {how_can_help}",
                "{greeting}! {enthusiasm} {intro} {ask_need}"
            ]
        },
        "presence_check": {
            "patterns": [
                r"(ci sei|sei qui|sei presente|sei online|are you there|you there)",
                r"(ci sei ancora|sei ancora qui)",
                r"(dimmi se ci sei|rispondi|answer)"
            ],
            "templates": [
                "{positive_response}! {reassurance} {offer_help}",
                "Sì, {present}! {enthusiasm} {how_can_help}",
                "{yes}, ci sono! {status} {ready_to_help}",
                "Certamente! {present_and_ready} {ask_question}"
            ]
        },
        "thanks": {
            "patterns": [
                r"(grazie|grazie mille|grazie infinite|thank you|thanks|thx|ty)",
                r"(molto gentile|sei gentile|sei stato gentile)",
                r"(prego di no|non c'è bisogno)"
            ],
            "templates": [
                "{welcome_response}! {happiness} {additional_offer}",
                "{welcome}! {pleasure} {always_available}",
                "{polite_response} {glad_to_help} {ask_more}"
            ]
        },
        "goodbye": {
            "patterns": [
                r"(arrivederci|ciao|a presto|ci sentiamo|goodbye|bye|see you)",
                r"(buona giornata|buona serata|buona notte|good night)",
                r"(a dopo|ci sentiamo dopo|talk later)"
            ],
            "templates": [
                "{goodbye_response}! {wish_well} {final_offer}",
                "{farewell}! {availability} {thanks}",
                "{goodbye}! {pleasant_wish} {come_back}"
            ]
        },
        "how_are_you": {
            "patterns": [
                r"(come stai|come va|tutto bene|how are you|how's it going)",
                r"(come procede|come vanno le cose|how are things)",
                r"(tutto ok|tutto a posto|everything ok)"
            ],
            "templates": [
                "{well_response}! {status_update} {focus_on_user}",
                "{positive_status}! {efficiency} {ready_to_help}",
                "Tutto {excellent}, grazie! {user_focus} {offer_assistance}"
            ]
        },
        "help_request": {
            "patterns": [
                r"(aiuto|help|aiutami|help me|ho bisogno|i need)",
                r"(non capisco|non so|non riesco|i don't understand)",
                r"(cosa posso fare|what can i do|come posso|how can i)"
            ],
            "templates": [
                "{helpful_response}! {explanation} {examples}",
                "Certo! {assistance_offer} {guidance} {encourage}",
                "{supportive}! {system_capabilities} {suggest_use}"
            ]
        },
        "capabilities": {
            "patterns": [
                r"(cosa fai|cosa sai fare|what can you do|capabilities)",
                r"(sei in grado|puoi|can you|are you able)",
                r"(funzionalità|features|cosa offri)"
            ],
            "templates": [
                "{intro_capabilities}! {main_features} {examples} {invite_question}",
                "{system_description}! {key_abilities} {use_cases}",
                "{explanation}! {what_i_can} {encourage_to_ask}"
            ]
        },
        "apology": {
            "patterns": [
                r"(scusa|mi dispiace|sorry|my apologies|excuse me)",
                r"(pardon|chiedo scusa|i apologize)"
            ],
            "templates": [
                "{no_need_apology}! {understanding} {positive_response}",
                "{not_necessary}! {friendly_response} {how_can_help}",
                "{reassure}! {no_problem} {continue_helping}"
            ]
        },
        "confirmation": {
            "patterns": [
                r"(ok|okay|va bene|perfetto|bene|all right|alright)",
                r"(d'accordo|agreed|sounds good|got it)",
                r"(capito|understood|clear|crystal clear)"
            ],
            "templates": [
                "{positive_ack}! {readiness} {what_next}",
                "{confirmation}! {ready_status} {await_instruction}",
                "{acknowledge}! {prepared} {invite_action}"
            ]
        },
        "compliment": {
            "patterns": [
                r"(bravo|brava|ottimo|fantastico|great|excellent|awesome)",
                r"(sei bravo|sei ottimo|you're great|you're awesome)",
                r"(buon lavoro|good job|well done|nice work)"
            ],
            "templates": [
                "{gratitude}! {happiness} {continue_helping}",
                "{appreciate}! {motivation} {always_ready}",
                "{thank_compliment}! {purpose} {offer_more}"
            ]
        },
        "general_question": {
            "patterns": [
                r"(cosa|che|chi|dove|quando|perché|why|what|who|where|when)",
                r"(dimmi|raccontami|tell me|explain)",
                r"(vorrei sapere|i would like to know|i want to know)"
            ],
            "templates": [
                "{friendly_response}! {clarify} {offer_to_search}",
                "{helpful} {redirect_to_pa_questions} {suggest_example}",
                "{understand}! {focus_on_pa} {ready_to_search}"
            ]
        },
        "time_questions": {
            "patterns": [
                r"(che ora|che ore|what time|current time|ora attuale)",
                r"(quale data|che data|what date|today|oggi)",
                r"(quando|when)"
            ],
            "templates": [
                "{time_response}! {date_response} {back_to_help}",
                "Oggi è {current_date}. {time_info} {focus_on_pa}",
                "{date_time_info}! {redirection} {offer_assistance}"
            ]
        },
        "misc": {
            "patterns": [
                r".*",  # Catch-all
            ],
            "templates": [
                "{friendly_default}! {intro} {how_can_help}",
                "{welcoming}! {purpose} {invite_question}",
                "{helpful_default}! {offer_assistance} {examples}"
            ]
        }
    }
    
    # Variabili per i template (generano varietà)
    VARIABLES = {
        "greeting": [
            "Ciao", "Salve", "Buongiorno", "Buonasera", "Ciao ciao",
            "Hey", "Ehi", "Hi", "Hello", "Buon pomeriggio"
        ],
        "intro": [
            "Sono NATAN, il tuo assistente per i documenti della PA.",
            "Sono qui per aiutarti con domande sui documenti della PA.",
            "Mi occupo di aiutarti a trovare informazioni nei documenti della PA.",
            "Il mio compito è aiutarti a cercare informazioni nei documenti della PA.",
            "Sono il tuo assistente per domande sui documenti della PA."
        ],
        "how_can_help": [
            "Come posso aiutarti?",
            "In cosa posso esserti utile?",
            "Cosa vorresti sapere?",
            "Dimmi pure come posso aiutarti.",
            "Fammi una domanda e cercherò la risposta per te.",
            "C'è qualcosa di specifico che vorresti sapere?",
            "Hai qualche domanda sui documenti della PA?",
            "Che cosa ti interessa sapere?",
            "Su cosa posso aiutarti oggi?",
            "Quale informazione stai cercando?"
        ],
        "offer_help": [
            "Sono qui per aiutarti.",
            "Sono a tua disposizione.",
            "Posso aiutarti con domande sui documenti.",
            "Chiedimi pure quello che vuoi sapere.",
            "Sono pronto ad aiutarti."
        ],
        "positive_response": [
            "Sì", "Certamente", "Assolutamente sì", "Esatto", "Proprio così",
            "Sì, certo", "Sì, naturalmente", "Ovviamente", "Senza dubbio"
        ],
        "present": [
            "sono qui", "sono presente", "ci sono", "sono online", "sono disponibile",
            "sono connesso", "sono pronto", "sono attivo"
        ],
        "reassurance": [
            "Sono qui e pronto ad aiutarti.",
            "Sono sempre disponibile per te.",
            "Puoi contare su di me.",
            "Sono al tuo servizio.",
            "Non ti lascerò solo."
        ],
        "yes": [
            "Sì", "Sì sì", "Certamente", "Assolutamente", "Esatto",
            "Proprio così", "Ovviamente", "Senza dubbio", "Certo che sì"
        ],
        "status": [
            "Sto benissimo", "Tutto a posto", "Perfetto", "Tutto ok",
            "Tutto bene", "Funziono perfettamente", "Tutto in ordine"
        ],
        "ready_to_help": [
            "Come posso aiutarti?",
            "Dimmi come posso esserti utile.",
            "Cosa posso fare per te?",
            "In cosa posso essere utile?",
            "Quale domanda hai per me?",
            "C'è qualcosa che vuoi sapere?",
            "Hai qualche domanda?",
            "Che cosa ti serve?",
            "Come posso assisterti?",
            "Su cosa posso aiutarti?"
        ],
        "present_and_ready": [
            "sono qui e pronto",
            "ci sono e sono disponibile",
            "sono presente e attivo",
            "sono connesso e pronto ad aiutare"
        ],
        "ask_question": [
            "Fammi una domanda!",
            "Chiedimi quello che vuoi sapere!",
            "Dimmi cosa ti serve!",
            "Quale domanda hai per me?",
            "Cosa vorresti sapere?",
            "Che cosa ti interessa?",
            "Quale informazione cerchi?",
            "Su cosa posso aiutarti?",
            "Hai qualche domanda sui documenti?",
            "Fammi una domanda sui documenti della PA!"
        ],
        "welcome_response": [
            "Prego", "Di nulla", "Figurati", "Non c'è problema",
            "Non preoccuparti", "È un piacere", "Il piacere è mio",
            "Felice di aver aiutato", "Contento di essere utile",
            "Lieto di esserti stato utile"
        ],
        "happiness": [
            "Sono felice", "Mi fa piacere", "Sono contento", "Sono lieto",
            "È un piacere", "Mi rallegra", "Mi rende felice"
        ],
        "additional_offer": [
            "Se hai altre domande, sono qui!",
            "Se hai bisogno di altro, chiedi pure!",
            "Se c'è altro che posso fare, dimmelo!",
            "Se hai altre domande, non esitare!",
            "Se hai bisogno di altro, sono a disposizione!",
            "Se c'è altro, sono qui per te!",
            "Se serve altro, basta chiedere!",
            "Se hai altre domande, fammi sapere!",
            "Se hai bisogno di altro, non esitare a chiedere!",
            "Se c'è qualcos'altro, sono qui!"
        ],
        "welcome": [
            "Prego", "Di nulla", "Figurati", "Non c'è problema",
            "È un piacere aiutarti", "Il piacere è mio"
        ],
        "pleasure": [
            "È stato un piacere", "Sono contento", "Mi fa piacere",
            "Felice di aver potuto aiutare", "Lieto di esserti stato utile"
        ],
        "always_available": [
            "Se hai bisogno di altro, sono sempre qui.",
            "Sono sempre disponibile per altre domande.",
            "Non esitare a chiedermi altro quando vuoi.",
            "Sono sempre qui se hai altre domande.",
            "Puoi sempre contare su di me."
        ],
        "polite_response": [
            "Prego, figurati", "Di nulla, figurati", "Non c'è problema",
            "È stato un piacere", "Il piacere è tutto mio"
        ],
        "glad_to_help": [
            "Sono felice di aver potuto aiutarti.",
            "Contento di essere stato utile.",
            "Lieto di aver potuto assisterti.",
            "Mi fa piacere essere stato d'aiuto."
        ],
        "ask_more": [
            "Se hai altre domande, chiedi pure!",
            "C'è altro in cui posso aiutarti?",
            "Hai bisogno di altro?",
            "Altre domande per me?",
            "Posso aiutarti con altro?",
            "C'è qualcos'altro che ti interessa?",
            "Hai altre domande sui documenti?",
            "Vuoi sapere altro?",
            "C'è altro che posso fare per te?",
            "Qualcos'altro su cui posso aiutarti?"
        ],
        "goodbye_response": [
            "Arrivederci", "A presto", "Ciao", "Buona giornata",
            "A dopo", "Ci sentiamo", "Ci sentiamo presto", "A presto!"
        ],
        "wish_well": [
            "Buona giornata!", "Buona serata!", "Buona notte!",
            "Buon lavoro!", "Buon proseguimento!", "Tutto il meglio!",
            "Buona fortuna!", "Buon weekend!", "Buone vacanze!"
        ],
        "final_offer": [
            "Se hai bisogno, sono sempre qui!",
            "Se serve altro, non esitare a tornare!",
            "Sono sempre disponibile se hai domande!",
            "Torna quando vuoi per altre domande!",
            "Sono qui se hai bisogno di altro!"
        ],
        "farewell": [
            "Arrivederci", "A presto", "Ciao", "Ci sentiamo"
        ],
        "availability": [
            "Sono sempre qui se hai bisogno.",
            "Torna pure quando vuoi.",
            "Sono sempre disponibile.",
            "Ci sentiamo quando vuoi."
        ],
        "thanks": [
            "Grazie a te!", "Grazie per aver usato NATAN!",
            "Grazie per la domanda!", "Grazie!"
        ],
        "pleasant_wish": [
            "Buona giornata!", "Buona serata!", "Buona notte!",
            "Buon proseguimento!", "Tutto il meglio!"
        ],
        "come_back": [
            "Torna pure quando vuoi per altre domande!",
            "Sono sempre qui se hai bisogno!",
            "Ci sentiamo presto!",
            "Spero di rivederti presto!"
        ],
        "well_response": [
            "Sto benissimo", "Tutto bene", "Perfetto", "Ottimo",
            "Tutto a posto", "Benissimo, grazie", "Tutto ok, grazie"
        ],
        "status_update": [
            "grazie per chiedere", "grazie dell'interesse",
            "grazie per la domanda", "grazie per l'interesse"
        ],
        "focus_on_user": [
            "E tu? Come stai?",
            "E tu, come va?",
            "Come posso aiutarti oggi?",
            "In cosa posso esserti utile?"
        ],
        "positive_status": [
            "Tutto perfetto", "Tutto ottimo", "Tutto benissimo",
            "Funziono alla perfezione", "Tutto in ordine"
        ],
        "efficiency": [
            "Sono efficiente e pronto",
            "Sono operativo al 100%",
            "Funziono perfettamente",
            "Sono al top delle prestazioni"
        ],
        "excellent": [
            "perfetto", "ottimo", "benissimo", "splendido",
            "fantastico", "alla grande", "eccellente"
        ],
        "user_focus": [
            "E tu? Come posso aiutarti?",
            "E tu? Hai qualche domanda?",
            "E tu? Cosa ti serve?",
            "E tu? C'è qualcosa che posso fare per te?"
        ],
        "offer_assistance": [
            "Come posso aiutarti?",
            "Cosa posso fare per te?",
            "In cosa posso essere utile?",
            "Quale assistenza ti serve?"
        ],
        "helpful_response": [
            "Certamente", "Certo", "Sì, certo", "Naturalmente",
            "Ovviamente", "Senza dubbio", "Assolutamente"
        ],
        "explanation": [
            "Sono qui per aiutarti con domande sui documenti della PA.",
            "Il mio compito è aiutarti a trovare informazioni nei documenti della PA.",
            "Posso aiutarti a cercare informazioni nei documenti della PA.",
            "Sono specializzato nell'aiutarti con domande sui documenti della PA."
        ],
        "examples": [
            "Puoi chiedermi qualsiasi cosa sui documenti: delibere, atti, bandi, ecc.",
            "Puoi farmi domande su delibere, atti amministrativi, bandi di gara, ecc.",
            "Ad esempio, puoi chiedermi informazioni su delibere, atti, bandi, ecc.",
            "Posso cercare informazioni su delibere, atti amministrativi, documenti vari, ecc."
        ],
        "assistance_offer": [
            "sono qui per aiutarti",
            "posso assisterti",
            "posso darti una mano",
            "sono a tua disposizione"
        ],
        "guidance": [
            "Fammi una domanda sui documenti della PA e cercherò la risposta per te.",
            "Chiedimi quello che vuoi sapere sui documenti e lo cercherò.",
            "Dimmi cosa ti serve sapere e lo cercherò nei documenti.",
            "Fammi una domanda specifica e la cercherò per te."
        ],
        "encourage": [
            "Non esitare a chiedere!",
            "Sentiti libero di farmi qualsiasi domanda!",
            "Chiedi pure, sono qui per questo!",
            "Fammi sapere cosa ti serve!"
        ],
        "supportive": [
            "Certamente", "Certo che sì", "Naturalmente", "Ovviamente",
            "Senza dubbio", "Assolutamente sì"
        ],
        "system_capabilities": [
            "Posso cercare informazioni nei documenti della PA per te.",
            "Il mio compito è aiutarti a trovare informazioni nei documenti.",
            "Sono in grado di cercare informazioni nei documenti della PA.",
            "Posso assisterti nella ricerca di informazioni nei documenti."
        ],
        "suggest_use": [
            "Prova a farmi una domanda!",
            "Fammi una domanda e vedrai!",
            "Chiedimi qualcosa sui documenti!",
            "Prova a chiedermi qualcosa!"
        ],
        "intro_capabilities": [
            "Sono NATAN, un assistente specializzato nei documenti della PA.",
            "Sono qui per aiutarti a trovare informazioni nei documenti della PA.",
            "Il mio compito è aiutarti con domande sui documenti della PA.",
            "Sono un assistente per la ricerca di informazioni nei documenti della PA."
        ],
        "main_features": [
            "Posso cercare informazioni in delibere, atti amministrativi, bandi di gara e altri documenti.",
            "Sono in grado di cercare informazioni su vari tipi di documenti della PA.",
            "Posso aiutarti a trovare informazioni su delibere, atti, bandi, ecc.",
            "Sono specializzato nella ricerca di informazioni nei documenti della PA."
        ],
        "invite_question": [
            "Prova a farmi una domanda!",
            "Fammi una domanda e vedrai cosa posso fare!",
            "Chiedimi qualcosa e lo cercherò per te!",
            "Fammi una domanda sui documenti!"
        ],
        "system_description": [
            "Sono un assistente per i documenti della PA.",
            "Sono qui per aiutarti con domande sui documenti.",
            "Il mio compito è aiutarti a trovare informazioni nei documenti.",
            "Sono specializzato nell'assistenza sui documenti della PA."
        ],
        "key_abilities": [
            "Posso cercare informazioni, rispondere a domande, trovare documenti specifici.",
            "Sono in grado di cercare, analizzare e rispondere a domande sui documenti.",
            "Posso cercare informazioni e rispondere a domande sui documenti.",
            "Sono in grado di cercare e trovare informazioni nei documenti."
        ],
        "use_cases": [
            "Puoi chiedermi informazioni su delibere, atti, bandi, ecc.",
            "Ad esempio, puoi chiedermi domande su delibere, atti amministrativi, ecc.",
            "Puoi farmi domande su vari tipi di documenti della PA.",
            "Posso aiutarti con domande su diversi tipi di documenti."
        ],
        "what_i_can": [
            "Posso cercare informazioni nei documenti della PA per te.",
            "Sono in grado di cercare e trovare informazioni nei documenti.",
            "Posso aiutarti a trovare informazioni nei documenti.",
            "Sono specializzato nella ricerca di informazioni nei documenti."
        ],
        "encourage_to_ask": [
            "Fammi una domanda e vedrai!",
            "Prova a chiedermi qualcosa!",
            "Chiedimi quello che vuoi sapere!",
            "Fammi una domanda sui documenti!"
        ],
        "no_need_apology": [
            "Non c'è bisogno di scusarsi",
            "Non devi scusarti",
            "Non c'è problema",
            "Figurati, non serve scusarsi"
        ],
        "understanding": [
            "Capisco perfettamente.",
            "Nessun problema.",
            "Non ti preoccupare.",
            "È normale."
        ],
        "not_necessary": [
            "Non è necessario",
            "Non serve",
            "Non c'è bisogno",
            "Non ti preoccupare"
        ],
        "friendly_response": [
            "Figurati",
            "Non c'è problema",
            "Tutto ok",
            "Nessun problema"
        ],
        "reassure": [
            "Tranquillo",
            "Non ti preoccupare",
            "È tutto ok",
            "Non c'è problema"
        ],
        "no_problem": [
            "non c'è problema",
            "non ti preoccupare",
            "figurati",
            "è tutto ok"
        ],
        "continue_helping": [
            "Come posso aiutarti?",
            "Dimmi come posso esserti utile.",
            "Cosa posso fare per te?",
            "In cosa posso essere utile?"
        ],
        "positive_ack": [
            "Perfetto", "Ottimo", "Bene", "Va bene",
            "Capito", "Chiaro", "D'accordo", "Perfetto così"
        ],
        "readiness": [
            "Sono pronto",
            "Sono qui",
            "Sono a tua disposizione",
            "Sono disponibile"
        ],
        "what_next": [
            "Come posso aiutarti?",
            "Cosa posso fare per te?",
            "In cosa posso essere utile?",
            "Quale domanda hai per me?"
        ],
        "confirmation": [
            "Perfetto", "Ottimo", "Bene", "Va bene",
            "Chiaro", "D'accordo", "Ok, capito"
        ],
        "ready_status": [
            "sono pronto ad aiutarti",
            "sono qui per te",
            "sono a tua disposizione",
            "sono disponibile"
        ],
        "await_instruction": [
            "Dimmi pure come posso aiutarti.",
            "Cosa posso fare per te?",
            "Quale domanda hai per me?",
            "In cosa posso essere utile?"
        ],
        "acknowledge": [
            "Capito", "Chiaro", "D'accordo", "Ok",
            "Perfetto", "Bene", "Va bene"
        ],
        "prepared": [
            "sono pronto",
            "sono qui",
            "sono a tua disposizione",
            "sono disponibile"
        ],
        "invite_action": [
            "Dimmi cosa posso fare per te!",
            "Come posso aiutarti?",
            "Cosa ti serve?",
            "Quale domanda hai?"
        ],
        "gratitude": [
            "Grazie", "Grazie mille", "Grazie infinite",
            "Ti ringrazio", "Grazie per il complimento"
        ],
        "motivation": [
            "Questo mi motiva a continuare",
            "Questo mi rende felice",
            "Questo mi incoraggia",
            "Questo mi gratifica"
        ],
        "appreciate": [
            "Apprezzo molto",
            "Grazie per",
            "Sono grato per",
            "Mi fa piacere sentire"
        ],
        "thank_compliment": [
            "Grazie per il complimento",
            "Grazie, sei molto gentile",
            "Grazie, mi fa piacere",
            "Ti ringrazio"
        ],
        "purpose": [
            "Il mio scopo è aiutarti",
            "Il mio compito è assisterti",
            "Sono qui per questo",
            "È questo il mio scopo"
        ],
        "offer_more": [
            "Se hai bisogno di altro, sono qui!",
            "Se c'è altro, chiedi pure!",
            "Se hai altre domande, non esitare!",
            "Sono sempre disponibile per altre domande!"
        ],
        "friendly_default": [
            "Ciao", "Salve", "Buongiorno", "Buonasera",
            "Ciao ciao", "Hey", "Ehi"
        ],
        "welcoming": [
            "Benvenuto", "Benvenuta", "Benvenuti",
            "Ciao e benvenuto", "Salve e benvenuto"
        ],
        "purpose": [
            "Sono qui per aiutarti con domande sui documenti della PA.",
            "Il mio compito è aiutarti a trovare informazioni nei documenti.",
            "Posso aiutarti con domande sui documenti della PA.",
            "Sono specializzato nell'aiutarti con i documenti della PA."
        ],
        "invite_question": [
            "Fammi una domanda!",
            "Chiedimi quello che vuoi sapere!",
            "Dimmi cosa ti serve!",
            "Quale domanda hai per me?"
        ],
        "helpful_default": [
            "Certo", "Naturalmente", "Ovviamente",
            "Senza dubbio", "Assolutamente"
        ],
        # Note: time_based_greeting is handled specially in _replace_variables
        "time_based_greeting": [],  # Placeholder - replaced dynamically
        "name": [
            "NATAN", "il tuo assistente NATAN",
            "NATAN, il tuo assistente", "l'assistente NATAN"
        ],
        "enthusiasm": [
            "Con piacere", "Volentieri", "Con gioia",
            "Con entusiasmo", "Con piacere"
        ],
        "current_date": [
            datetime.now().strftime("%d/%m/%Y"),
            datetime.now().strftime("%A %d %B %Y"),
            f"il {datetime.now().strftime('%d/%m/%Y')}"
        ],
        "date_time_info": [
            f"Oggi è {datetime.now().strftime('%d/%m/%Y')} e sono le {datetime.now().strftime('%H:%M')}",
            f"La data di oggi è {datetime.now().strftime('%d/%m/%Y')}",
            f"Siamo il {datetime.now().strftime('%d %B %Y')}"
        ],
        "time_info": [
            f"Sono le {datetime.now().strftime('%H:%M')}",
            f"L'ora attuale è {datetime.now().strftime('%H:%M')}"
        ],
        "redirection": [
            "Ma il mio compito è aiutarti con domande sui documenti della PA.",
            "Però posso aiutarti meglio con domande sui documenti.",
            "Ma sono qui principalmente per aiutarti con i documenti della PA."
        ],
        "back_to_help": [
            "Ma posso aiutarti meglio con domande sui documenti della PA!",
            "Però sono qui per aiutarti con i documenti!",
            "Ma il mio compito è aiutarti con i documenti della PA!"
        ],
        "clarify": [
            "Per aiutarti meglio, potresti farmi una domanda specifica sui documenti della PA?",
            "Per essere più preciso, potresti chiedermi qualcosa sui documenti?",
            "Per risponderti meglio, hai qualche domanda sui documenti della PA?"
        ],
        "redirect_to_pa_questions": [
            "Sono specializzato in domande sui documenti della PA.",
            "Il mio compito è aiutarti con domande sui documenti della PA.",
            "Sono qui per aiutarti con i documenti della PA."
        ],
        "suggest_example": [
            "Ad esempio, puoi chiedermi informazioni su delibere, atti, bandi, ecc.",
            "Prova a chiedermi qualcosa sui documenti della PA!",
            "Fammi una domanda specifica sui documenti!"
        ],
        "focus_on_pa": [
            "Sono qui principalmente per aiutarti con domande sui documenti della PA.",
            "Il mio compito principale è aiutarti con i documenti della PA.",
            "Sono specializzato in domande sui documenti della PA."
        ],
        "ready_to_search": [
            "Fammi una domanda e cercherò la risposta per te!",
            "Chiedimi qualcosa sui documenti e lo cercherò!",
            "Fammi una domanda specifica e la cercherò nei documenti!"
        ],
        "understand": [
            "Capisco", "Capisco perfettamente", "Comprendo",
            "Ho capito", "Capito"
        ]
    }
    
    @staticmethod
    def _get_time_based_greeting() -> str:
        """Generate time-based greeting"""
        hour = datetime.now().hour
        if 5 <= hour < 12:
            return "Buongiorno"
        elif 12 <= hour < 18:
            return "Buon pomeriggio"
        elif 18 <= hour < 22:
            return "Buonasera"
        else:
            return "Buona notte"
    
    @classmethod
    def generate_response(cls, question: str) -> str:
        """
        Generate a conversational response based on pattern matching
        
        Args:
            question: User question
            
        Returns:
            Generated response string or None if no match found (triggers AI learning)
        """
        question_lower = question.lower().strip()
        
        # Try to match categories in order
        for category_name, category_data in cls.CATEGORIES.items():
            if category_name == "misc":
                continue  # Check misc last
            
            patterns = category_data["patterns"]
            templates = category_data["templates"]
            
            # Check if question matches any pattern
            for pattern in patterns:
                if re.search(pattern, question_lower, re.IGNORECASE):
                    # Select random template
                    template = random.choice(templates)
                    
                    # Replace variables in template
                    response = cls._replace_variables(template)
                    
                    return response
        
        # No match found - return None to trigger AI learning
        return None
    
    @classmethod
    def _replace_variables(cls, template: str) -> str:
        """
        Replace variables in template with random values
        
        Args:
            template: Template string with {variable} placeholders
            
        Returns:
            String with replaced variables
        """
        # Handle special variables that need computation first
        if "{time_based_greeting}" in template:
            template = template.replace("{time_based_greeting}", cls._get_time_based_greeting())
        if "{current_date}" in template:
            template = template.replace("{current_date}", datetime.now().strftime("%d/%m/%Y"))
        if "{date_time_info}" in template:
            template = template.replace(
                "{date_time_info}",
                f"Oggi è {datetime.now().strftime('%d/%m/%Y')} e sono le {datetime.now().strftime('%H:%M')}"
            )
        if "{time_info}" in template:
            template = template.replace("{time_info}", f"Sono le {datetime.now().strftime('%H:%M')}")
        
        # Replace all other variables (iterate multiple times to handle nested replacements)
        max_iterations = 10
        for _ in range(max_iterations):
            if "{" not in template:
                break  # No more placeholders
            
            found_replacement = False
            for var_name, var_values in cls.VARIABLES.items():
                if not var_values:  # Skip empty variable lists
                    continue
                placeholder = f"{{{var_name}}}"
                if placeholder in template:
                    value = random.choice(var_values)
                    template = template.replace(placeholder, value)
                    found_replacement = True
            
            if not found_replacement:
                break  # No more replacements possible
        
        # Clean up any remaining placeholders (fallback)
        import re
        remaining_placeholders = re.findall(r'\{([^}]+)\}', template)
        for placeholder in remaining_placeholders:
            # Use a generic fallback
            template = template.replace(f"{{{placeholder}}}", "")
        
        # Clean up extra spaces
        template = re.sub(r'\s+', ' ', template).strip()
        
        return template

