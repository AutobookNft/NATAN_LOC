import unittest
import sys
import os

# Aggiunge la root al path per importare il modulo
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))

from python_ai_service.toon_utils import ToonConverter

class TestToonConverter(unittest.TestCase):
    
    def test_conversion_integrity(self):
        """Verifica che convertendo JSON -> TOON -> JSON i dati rimangano identici"""
        original_data = [
            {"id": 1, "name": "Firenze", "active": True},
            {"id": 2, "name": "Pisa", "active": False},
            {"id": 3, "name": "Roma", "active": True}
        ]
        
        # 1. Conversione in TOON
        toon_output = ToonConverter.to_toon(original_data, "cities")
        print(f"\n[DEBUG] TOON Generato:\n{toon_output}")
        
        # Verifica formato header
        self.assertTrue(toon_output.startswith("cities[3]{id,name,active}:"))
        
        # 2. Ripristino in JSON (Lista di dict)
        restored_data = ToonConverter.from_toon(toon_output)
        
        # 3. Verifica uguaglianza profonda
        self.assertEqual(len(restored_data), 3)
        self.assertEqual(restored_data[0]['name'], "Firenze")
        self.assertEqual(restored_data[0]['id'], 1)     # Verifica che int sia rimasto int
        self.assertEqual(restored_data[0]['active'], True) # Verifica che bool sia rimasto bool
        
        self.assertEqual(original_data, restored_data)
        print("\n[SUCCESS] Test di integrità superato: Nessuna perdita di dati.")

if __name__ == '__main__':
    unittest.main()
