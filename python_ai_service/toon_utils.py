import re

class ToonConverter:
    """
    Gestisce la conversione tra lista di dizionari (JSON-like) e formato TOON.
    Ottimizzato per ridurre i token per gli LLM.
    """

    @staticmethod
    def to_toon(data: list, root_name: str = "items") -> str:
        """
        Converte una lista di dizionari piatti in stringa TOON.
        Esempio input: [{"a": 1}, {"a": 2}]
        Esempio output: items[2]{a}:\n1\n2
        """
        if not data or not isinstance(data, list):
            return ""

        # Prende le chiavi dal primo elemento per definire lo schema
        keys = list(data[0].keys())
        count = len(data)
        
        # Header formato: root[count]{key1,key2}:
        header = f"{root_name}[{count}]{{{','.join(keys)}}}:"
        lines = [header]
        
        for item in data:
            row_values = []
            for k in keys:
                val = item.get(k, "")
                # SANITIZZAZIONE: 
                # 1. Rimuoviamo newline per mantenere la struttura a righe
                # 2. Sostituiamo le virgole nei valori con punto e virgola per non rompere il CSV
                val_str = str(val).replace("\n", " ").replace(",", ";") 
                row_values.append(val_str)
            lines.append(",".join(row_values))
            
        return "\n".join(lines)

    @staticmethod
    def from_toon(toon_str: str) -> list:
        """
        Converte una stringa TOON in lista di dizionari, tentando di ripristinare i tipi.
        """
        # Pulisce le righe vuote
        lines = [l.strip() for l in toon_str.strip().split('\n') if l.strip()]
        if not lines:
            return []

        header = lines[0]
        # Regex per parsare l'header: name[count]{k1,k2}:
        match = re.match(r"(\w+)\[(\d+)\]\{(.+)\}:", header)
        
        if not match:
            # Se l'header non è valido, ritorna lista vuota o solleva eccezione a seconda della policy
            return []
            
        _, _, keys_str = match.groups()
        keys = keys_str.split(',')
        
        result = []
        # Itera sui dati (dalla seconda riga in poi)
        for line in lines[1:]:
            values = line.split(',')
            obj = {}
            for i, key in enumerate(keys):
                if i < len(values):
                    val = values[i]
                    # TENTATIVO DI RIPRISTINO TIPI (Type Inference)
                    # Questo riduce il rischio di avere tutto come stringa
                    if val.isdigit():
                        val = int(val)
                    elif val.replace('.', '', 1).isdigit() and '.' in val:
                        try:
                            val = float(val)
                        except ValueError:
                            pass
                    elif val.lower() == 'true':
                        val = True
                    elif val.lower() == 'false':
                        val = False
                    
                    obj[key] = val
            result.append(obj)
            
        return result
