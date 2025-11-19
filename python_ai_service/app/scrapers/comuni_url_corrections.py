# URL CORRETTI COMUNI TOSCANA
# Verificati il 14/11/2025 con verifica_url_comuni.py

CORREZIONI_URL = {
    # URL SBAGLIATI DA CORREGGERE
    'Firenze': {
        'url_vecchio': 'https://www.comune.fi.it',
        'url_corretto': 'https://www.comune.firenze.it',
        'note': 'Redirect permanente'
    },
    'Empoli': {
        'url_vecchio': 'https://www.empoli.gov.it',
        'url_corretto': 'https://www.comune.empoli.fi.it',
        'note': 'URL .gov.it non raggiungibile - CONNECTION_ERROR'
    },
    'Sesto Fiorentino': {
        'url_vecchio': 'https://www.sestofiorentino.it',
        'url_corretto': 'https://www.sestofiorentino.it',  # URL base 403
        'subdomain_albo': 'http://servizi.comune.sesto-fiorentino.fi.it',
        'note': 'URL base 403 Forbidden, usare subdomain servizi'
    }
}

# LINK ALBO PRETORIO DIRETTI (trovati su homepage)
LINK_ALBO_DIRETTI = {
    'Firenze': 'https://www.comune.firenze.it/albo-pretorio',
    'Scandicci': 'https://scandicci.soluzionipa.it/openweb/albo/albo_pretorio.php',
    'Campi Bisenzio': 'https://campibisenzio.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio',
    'Bagno a Ripoli': 'https://trasparenza.comune.bagno-a-ripoli.fi.it/web/trasparenza/menu-albo-pretorio',
    'Fiesole': 'https://fiesole.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio',
    'Pontassieve': 'https://pubblicazioni.comune.pontassieve.fi.it/web/trasparenza/albo-pretorio',
    'Borgo San Lorenzo': 'https://borgosanlorenzo.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio',
    'Calenzano': 'https://www.comune.calenzano.fi.it/it/page/80188',
    'Empoli': 'https://empoli.trasparenza-valutazione-merito.it/web/trasparenza/albo-pretorio'
}

# API ENDPOINT VERIFICATI
API_ENDPOINT_NOTI = {
    'Firenze': {
        'url': 'https://accessoconcertificato.comune.fi.it/trasparenza-atti-cat/searchAtti',
        'tipo': 'API_REST',
        'atti_trovati': 415,
        'data_verifica': '14/11/2025'
    },
    'Sesto Fiorentino': {
        'url': 'http://servizi.comune.sesto-fiorentino.fi.it/albo/search.php',
        'tipo': 'API_DATATABLES',
        'atti_trovati': 117,
        'data_verifica': '14/11/2025'
    }
}

# PIATTAFORME IDENTIFICATE
PIATTAFORME_COMUNI = {
    'SoluzioniPA': ['Scandicci'],
    'Trasparenza-Valutazione-Merito': [
        'Campi Bisenzio',
        'Bagno a Ripoli', 
        'Fiesole',
        'Pontassieve',
        'Borgo San Lorenzo',
        'Empoli'
    ]
}

def get_url_corretto(nome_comune: str, url_originale: str) -> str:
    """Restituisce URL corretto per il comune"""
    if nome_comune in CORREZIONI_URL:
        correzione = CORREZIONI_URL[nome_comune]
        if 'subdomain_albo' in correzione:
            return correzione['subdomain_albo']
        return correzione['url_corretto']
    return url_originale

def get_link_albo(nome_comune: str) -> str:
    """Restituisce link diretto albo pretorio"""
    return LINK_ALBO_DIRETTI.get(nome_comune)

def get_api_endpoint(nome_comune: str) -> dict:
    """Restituisce API endpoint noto se esiste"""
    return API_ENDPOINT_NOTI.get(nome_comune)
