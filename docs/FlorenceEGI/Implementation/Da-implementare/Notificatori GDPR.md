# Nota per TODO Post-MVP: Notificatori GDPR

## 📋 **TODO POST-MVP: Sistema Notificazioni GDPR**

### **🎯 Stato Attuale**

Il sistema GDPR è **funzionalmente completo** ma manca il layer di notificazioni utente. La configurazione in `config/gdpr.php` sezione `notifications` è stata **temporaneamente commentata** per evitare errori di classi mancanti.

### **📧 Notificatori da Implementare**

```php
// Classes da creare in App\Notifications\Gdpr\:
- ConsentUpdatedNotification::class           // Consensi modificati
- DataExportedNotification::class             // Export dati pronto  
- ProcessingRestrictedNotification::class     // Limitazioni attivate
- AccountDeletionRequestedNotification::class // Richiesta cancellazione
- AccountDeletionProcessedNotification::class // Cancellazione completata
- BreachReportReceivedNotification::class     // Violazione ricevuta
```

### **🔍 Research Necessaria**

Prima dell'implementazione:

1. **Analizzare sistema notifiche esistente** in FlorenceEGI
2. **Identificare pattern di notification** già utilizzati
3. **Verificare integrazione email/Slack** esistente
4. **Controllare template engine** per consistency

### **🎨 Requisiti Implementazione**

- **Brand Compliance**: Template FlorenceEGI (oro fiorentino, rinascimento)
- **Multilingua**: Supporto IT/EN
- **GDPR Compliant**: Contenuto minimal, link a privacy center
- **Responsive**: Email template mobile-friendly
- **Testing**: Test suite per tutte le notifiche

### **⚡ Priorità Post-MVP**

1. **Alta**: `DataExportedNotification` (UX critica)
2. **Alta**: `AccountDeletionRequestedNotification` (compliance)
3. **Media**: `ConsentUpdatedNotification` (conferma UX)
4. **Bassa**: Altre notifiche (nice-to-have)

### **🔧 Note Tecniche**

- Configurazione pronta in `config/gdpr.php` (ora commentata)
- GdprController già prevede trigger points
- Slack webhook configurabile via ENV
- Rate limiting da considerare per anti-spam

### **📅 Stima Implementazione**

- **Research pattern esistenti**: 4 ore
- **Template design**: 8 ore
- **6 Notification classes**: 12 ore
- **Testing completo**: 6 ore
- **Total**: ~30 ore (1 settimana)

### **🚨 Reminder Importante**

Decommentare sezione `notifications` in `config/gdpr.php` solo **dopo** aver creato tutte le classi per evitare ClassNotFoundException.

---

_Aggiunto il: 25 Maggio 2025_  
_Priorità: Post-MVP_  
_Owner: Backend Team_  
_Dependency: Sistema notifiche FlorenceEGI analysis_