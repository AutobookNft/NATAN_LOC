"""
Comuni Scraping Tracker - Traccia comuni già scrapati per evitare duplicati
e generare report per comuni già processati.

Salva in MongoDB collection `scraped_comuni` con struttura:
{
    "comune_slug": "firenze",
    "comune_nome": "Firenze",
    "scraped_at": "2025-01-28T10:30:00Z",
    "atti_estratti": 2275,
    "compliance_score": 0.85,
    "violations_count": 2,
    "metodo_usato": "API REST Firenze (diretta)",
    "json_backup_path": "storage/testing/compliance_scanner/json/atti_firenze_20250128_103000.json",
    "pdf_report_path": "storage/testing/compliance_scanner/reports/firenze_20250128_103000.pdf",
    "landing_page_url": "https://...",
    "email_sent": true,
    "tenant_id": 1,
    "status": "completed"  # completed, failed, partial
}
"""

import logging
from typing import Dict, List, Optional
from datetime import datetime
from app.services.mongodb_service import MongoDBService

logger = logging.getLogger(__name__)


class ComuniScrapingTracker:
    """
    Traccia comuni già scrapati per evitare duplicati e generare report.
    
    Usa MongoDB collection `scraped_comuni` per persistenza multi-tenant.
    """
    
    COLLECTION_NAME = "scraped_comuni"
    
    @classmethod
    def _ensure_indexes(cls):
        """Crea indici MongoDB per performance"""
        try:
            if MongoDBService.is_connected():
                collection = MongoDBService.get_collection(cls.COLLECTION_NAME)
                if collection:
                    # Indice unico su comune_slug + tenant_id (evita duplicati)
                    collection.create_index([("comune_slug", 1), ("tenant_id", 1)], unique=True)
                    # Indice su scraped_at per ordinamento
                    collection.create_index([("scraped_at", -1)])
                    # Indice su status per filtri
                    collection.create_index([("status", 1)])
        except Exception as e:
            logger.debug(f"Errore creazione indici MongoDB: {e}")
    
    @classmethod
    def mark_comune_scraped(
        cls,
        comune_slug: str,
        comune_nome: Optional[str] = None,
        atti_estratti: int = 0,
        compliance_score: Optional[float] = None,
        violations_count: int = 0,
        metodo_usato: Optional[str] = None,
        json_backup_path: Optional[str] = None,
        pdf_report_path: Optional[str] = None,
        landing_page_url: Optional[str] = None,
        email_sent: bool = False,
        tenant_id: Optional[int] = None,
        status: str = "completed"
    ) -> bool:
        """
        Marca un comune come già scrapato.
        
        Args:
            comune_slug: Slug del comune (es: "firenze")
            comune_nome: Nome completo del comune (es: "Firenze")
            atti_estratti: Numero di atti estratti
            compliance_score: Score di conformità (0-1)
            violations_count: Numero di violazioni rilevate
            metodo_usato: Metodo di scraping usato
            json_backup_path: Path del file JSON backup
            pdf_report_path: Path del report PDF generato
            landing_page_url: URL della landing page generata
            email_sent: Se email è stata inviata
            tenant_id: ID tenant (opzionale)
            status: Status scraping (completed, failed, partial)
            
        Returns:
            True se salvato con successo, False altrimenti
        """
        try:
            cls._ensure_indexes()
            
            document = {
                "comune_slug": comune_slug.lower().strip(),
                "comune_nome": comune_nome or comune_slug.title(),
                "scraped_at": datetime.now(),
                "atti_estratti": atti_estratti,
                "compliance_score": compliance_score,
                "violations_count": violations_count,
                "metodo_usato": metodo_usato,
                "json_backup_path": json_backup_path,
                "pdf_report_path": pdf_report_path,
                "landing_page_url": landing_page_url,
                "email_sent": email_sent,
                "tenant_id": tenant_id,
                "status": status,
                "updated_at": datetime.now()
            }
            
            # Usa update con upsert per aggiornare se esiste già
            if MongoDBService.is_connected():
                collection = MongoDBService.get_collection(cls.COLLECTION_NAME)
                if collection is not None:
                    try:
                        # Cerca documento esistente
                        existing = collection.find_one({
                            "comune_slug": comune_slug.lower().strip(),
                            "tenant_id": tenant_id
                        })
                        
                        if existing is not None:
                            # Aggiorna documento esistente
                            collection.update_one(
                                {"_id": existing["_id"]},
                                {"$set": document}
                            )
                            logger.info(f"✅ Aggiornato tracking comune: {comune_slug}")
                        else:
                            # Inserisci nuovo documento
                            result = MongoDBService.insert_document(cls.COLLECTION_NAME, document)
                            if result and result != 'duplicate':
                                logger.info(f"✅ Salvato tracking comune: {comune_slug}")
                            else:
                                logger.debug(f"Comune già tracciato: {comune_slug}")
                        
                        return True
                    except Exception as e:
                        logger.error(f"Errore operazione MongoDB per {comune_slug}: {e}")
                        return False
            
            # Fallback: salva in file JSON se MongoDB non disponibile
            logger.warning("MongoDB non disponibile, tracking non salvato")
            return False
            
        except Exception as e:
            logger.error(f"Errore salvataggio tracking comune {comune_slug}: {e}")
            return False
    
    @classmethod
    def is_comune_scraped(cls, comune_slug: str, tenant_id: Optional[int] = None) -> bool:
        """
        Verifica se un comune è già stato scrapato.
        
        Args:
            comune_slug: Slug del comune
            tenant_id: ID tenant (opzionale)
            
        Returns:
            True se già scrapato, False altrimenti
        """
        try:
            if not MongoDBService.is_connected():
                return False
            
            filter_query = {
                "comune_slug": comune_slug.lower().strip(),
                "status": {"$in": ["completed", "partial"]}  # Solo scraping completati o parziali
            }
            
            if tenant_id is not None:
                filter_query["tenant_id"] = tenant_id
            
            result = MongoDBService.find_documents(
                cls.COLLECTION_NAME,
                filter_query,
                limit=1
            )
            
            return len(result) > 0
            
        except Exception as e:
            logger.debug(f"Errore verifica comune scrapato {comune_slug}: {e}")
            return False
    
    @classmethod
    def get_scraped_comuni(cls, tenant_id: Optional[int] = None, status: Optional[str] = None) -> List[Dict]:
        """
        Ottiene lista di tutti i comuni già scrapati.
        
        Args:
            tenant_id: ID tenant per filtrare (opzionale)
            status: Filtra per status (completed, failed, partial) (opzionale)
            
        Returns:
            Lista di documenti comuni scrapati
        """
        try:
            if not MongoDBService.is_connected():
                return []
            
            filter_query = {}
            if tenant_id is not None:
                filter_query["tenant_id"] = tenant_id
            if status:
                filter_query["status"] = status
            
            result = MongoDBService.find_documents(
                cls.COLLECTION_NAME,
                filter_query,
                sort=[("scraped_at", -1)]  # Più recenti prima
            )
            
            return result
            
        except Exception as e:
            logger.error(f"Errore recupero comuni scrapati: {e}")
            return []
    
    @classmethod
    def get_unscraped_comuni(cls, comuni_list: List[str], tenant_id: Optional[int] = None) -> List[str]:
        """
        Filtra lista comuni restituendo solo quelli non ancora scrapati.
        
        Args:
            comuni_list: Lista di slug comuni da verificare
            tenant_id: ID tenant (opzionale)
            
        Returns:
            Lista di slug comuni non ancora scrapati
        """
        try:
            scraped = cls.get_scraped_comuni(tenant_id=tenant_id, status="completed")
            scraped_slugs = {doc["comune_slug"].lower() for doc in scraped}
            
            unscraped = [
                comune for comune in comuni_list
                if comune.lower().strip() not in scraped_slugs
            ]
            
            logger.info(f"Comuni già scrapati: {len(scraped_slugs)}, Non scrapati: {len(unscraped)}")
            return unscraped
            
        except Exception as e:
            logger.error(f"Errore filtraggio comuni non scrapati: {e}")
            return comuni_list  # In caso di errore, restituisci tutti
    
    @classmethod
    def get_comune_info(cls, comune_slug: str, tenant_id: Optional[int] = None) -> Optional[Dict]:
        """
        Ottiene informazioni dettagliate su un comune già scrapato.
        
        Args:
            comune_slug: Slug del comune
            tenant_id: ID tenant (opzionale)
            
        Returns:
            Documento con info comune o None se non trovato
        """
        try:
            if not MongoDBService.is_connected():
                return None
            
            filter_query = {
                "comune_slug": comune_slug.lower().strip()
            }
            
            if tenant_id is not None:
                filter_query["tenant_id"] = tenant_id
            
            result = MongoDBService.find_documents(
                cls.COLLECTION_NAME,
                filter_query,
                limit=1,
                sort=[("scraped_at", -1)]  # Più recente
            )
            
            return result[0] if result else None
            
        except Exception as e:
            logger.error(f"Errore recupero info comune {comune_slug}: {e}")
            return None
    
    @classmethod
    def get_comuni_stats(cls, tenant_id: Optional[int] = None) -> Dict:
        """
        Ottiene statistiche aggregate sui comuni scrapati.
        
        Args:
            tenant_id: ID tenant per filtrare (opzionale)
            
        Returns:
            Dict con statistiche (total_scraped, total_atti, avg_compliance_score, etc.)
        """
        try:
            scraped = cls.get_scraped_comuni(tenant_id=tenant_id)
            
            stats = {
                "total_scraped": len(scraped),
                "total_atti": sum(doc.get("atti_estratti", 0) for doc in scraped),
                "total_violations": sum(doc.get("violations_count", 0) for doc in scraped),
                "comuni_completed": len([d for d in scraped if d.get("status") == "completed"]),
                "comuni_partial": len([d for d in scraped if d.get("status") == "partial"]),
                "comuni_failed": len([d for d in scraped if d.get("status") == "failed"]),
            }
            
            # Calcola score medio
            scores = [d.get("compliance_score") for d in scraped if d.get("compliance_score") is not None]
            if scores:
                stats["avg_compliance_score"] = sum(scores) / len(scores)
            else:
                stats["avg_compliance_score"] = None
            
            return stats
            
        except Exception as e:
            logger.error(f"Errore calcolo statistiche: {e}")
            return {
                "total_scraped": 0,
                "total_atti": 0,
                "total_violations": 0,
                "avg_compliance_score": None
            }

