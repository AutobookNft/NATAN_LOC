<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-01
 * @purpose Controller per interfaccia chat NATAN con cronologia conversazioni
 */
class NatanChatController extends Controller
{
    /**
     * Show NATAN chat interface with conversation history
     *
     * Note: Auth gestita tramite EGI condiviso, quindi user può essere null
     *
     * @return View
     */
    public function index(): View
    {
        // Set locale to Italian (fallback to config default)
        App::setLocale(config('app.locale', 'it'));

        $user = Auth::user(); // Può essere null se non autenticato tramite EGI

        // Get chat history for current user (last 20 conversations, ordered by last_message_at DESC)
        // Se user è null, mostra cronologia vuota
        $chatHistory = [];

        if ($user) {
            // Get chat history from natan_chat_messages (existing table)
            // Group by session_id to get conversation list
            $sessions = \App\Models\NatanChatMessage::where('user_id', $user->id)
                ->select('session_id')
                ->selectRaw('MIN(created_at) as first_message_at')
                ->selectRaw('MAX(created_at) as last_message_at')
                ->selectRaw('COUNT(*) as message_count')
                ->selectRaw('MAX(persona_id) as persona')
                ->groupBy('session_id')
                ->orderBy('last_message_at', 'desc')
                ->limit(20)
                ->get();

            $chatHistory = $sessions->map(function ($session) use ($user) {
                // Get first user message for title
                $firstUserMessage = \App\Models\NatanChatMessage::where('session_id', $session->session_id)
                    ->where('user_id', $user->id)
                    ->where('role', 'user')
                    ->orderBy('created_at', 'asc')
                    ->first();

                $title = $firstUserMessage
                    ? (strlen($firstUserMessage->content) > 50
                        ? substr($firstUserMessage->content, 0, 50) . '...'
                        : $firstUserMessage->content)
                    : __('natan.history.untitled');

                // Calculate total cost for this session
                $totalCostEur = \App\Models\NatanChatMessage::getTotalCostForSession($session->session_id);

                return [
                    'id' => $session->session_id,
                    'title' => $title,
                    'date' => $session->last_message_at,
                    'message_count' => $session->message_count,
                    'cost_eur' => $totalCostEur,
                    'persona' => $session->persona ?? 'strategic',
                ];
            })->toArray();
        }

        // Get all strategic questions organized by category and flat array for random selection
        $strategicQuestionsLibrary = $this->getStrategicQuestionsLibrary();
        $allStrategicQuestionsFlat = $this->getAllStrategicQuestionsFlat();

        // Get 6 random questions for suggestions panel
        $suggestedQuestions = collect($allStrategicQuestionsFlat)->random(min(6, count($allStrategicQuestionsFlat)))->toArray();

        // Get total conversation count for memory badge
        $totalConversations = 0;
        if ($user) {
            // Count distinct sessions for this user
            $totalConversations = \App\Models\NatanChatMessage::where('user_id', $user->id)
                ->distinct('session_id')
                ->count('session_id');
        }

        return view('natan.chat', [
            'chatHistory' => $chatHistory,
            'suggestedQuestions' => $suggestedQuestions,
            'strategicQuestionsLibrary' => $strategicQuestionsLibrary, // All 63 questions organized by category
            'totalConversations' => $totalConversations,
        ]);
    }

    /**
     * Get all strategic questions from the library
     * These are engineered prompts that demonstrate best practices for writing effective NATAN queries
     *
     * Each question demonstrates:
     * - Specificity: Clear domain, timeframe, and filters
     * - Actionability: Requests concrete outputs (strategies, plans, models, analyses)
     * - Structured thinking: Uses frameworks, metrics, and evaluation criteria
     * - Domain expertise: Categorized by persona/domain specialization
     *
     * Total: 63 questions organized in 14 categories
     *
     * @return array Associative array with category keys and arrays of questions
     */
    private function getStrategicQuestionsLibrary(): array
    {
        return [
            // Strategic & Governance (4 questions)
            'strategic' => [
                "Analizza le principali aree di investimento del Comune negli ultimi 12 mesi e suggerisci una strategia di ottimizzazione basata su ROI e priorità strategiche",
                "Identifica i ritardi nei progetti PNRR e proponi un piano di recovery con milestone specifiche e azioni correttive immediate",
                "Crea una matrice decisionale per prioritizzare i progetti in base a impatto, urgenza, costo e fattibilità tecnica",
                "Confronta le performance del Comune con best practices nazionali e internazionali, identificando gap e opportunità di miglioramento",
            ],

            // Technical (4 questions)
            'technical' => [
                "Valuta la fattibilità tecnica dei progetti infrastrutturali in corso e identifica rischi critici con strategie di mitigazione",
                "Analizza lo stato manutentivo delle infrastrutture pubbliche e proponi un piano di manutenzione predittiva basato su priorità",
                "Identifica progetti con problemi di compliance normativa tecnica e proponi azioni correttive con timeline",
                "Valuta le specifiche tecniche degli appalti recenti e suggerisci miglioramenti per future gare",
            ],

            // Financial (4 questions)
            'financial' => [
                "Analizza l'efficienza della spesa pubblica per settore, calcola costo per cittadino servito e identifica aree di ottimizzazione",
                "Identifica tutte le opportunità di funding EU disponibili (PNRR, PON, FSE) e valuta quali progetti possono candidarsi",
                "Crea un modello finanziario NPV/IRR per i progetti a lungo termine e calcola il break-even point",
                "Analizza il budget variance degli ultimi 3 anni e proponi strategie per migliorare la previsione finanziaria",
            ],

            // Legal (4 questions)
            'legal' => [
                "Verifica la compliance GDPR di tutti gli atti che trattano dati personali e identifica eventuali violazioni con azioni correttive",
                "Analizza i procedimenti amministrativi con rischio contenzioso e proponi strategie di de-risking legale",
                "Identifica tutti gli atti con problemi di trasparenza o anticorruzione secondo la normativa vigente",
                "Valuta la regolarità delle procedure di gara recenti e suggerisci best practices per future procedure",
            ],

            // Urban & Social (4 questions)
            'urban_social' => [
                "Analizza l'impatto sociale dei progetti di rigenerazione urbana e calcola il SROI (Social Return on Investment)",
                "Identifica le aree sottoutilizzate della città e proponi strategie di riqualificazione con focus su accessibilità e inclusione",
                "Valuta l'equità territoriale nella distribuzione dei servizi pubblici e proponi azioni per ridurre i gap",
                "Crea un piano di partecipazione cittadina per i prossimi progetti urbani con metodologie innovative",
            ],

            // Communication (4 questions)
            'communication' => [
                "Crea una strategia di comunicazione per annunciare i risultati dei progetti PNRR con key messages e piano media",
                "Identifica i progetti con maggior potenziale mediatico e sviluppa storytelling efficace per massimizzare l'impatto",
                "Analizza il sentiment pubblico sui progetti in corso e proponi strategie di engagement per aumentare il supporto",
                "Sviluppa un piano di crisis communication per gestire eventuali controversie su progetti sensibili",
            ],

            // Ricerca & Classificazione (5 questions)
            'search_classification' => [
                "Trova tutti gli atti relativi a manutenzione stradale e infrastrutture pubblicati tra gennaio 2023 e ottobre 2024",
                "Classifica gli atti per settore di intervento (urbanistica, sociale, ambiente, cultura) e mostra la distribuzione",
                "Identifica tutti gli atti che contengono codici CUP e raggruppali per tipologia di progetto",
                "Cerca atti relativi a progetti di rigenerazione urbana e crea un elenco strutturato con titoli e date",
                "Filtra gli atti per tipologia amministrativa (delibere, determinazioni, decreti) e mostra statistiche",
            ],

            // Analisi Temporale (5 questions)
            'temporal_analysis' => [
                "Analizza l'evoluzione dell'attività normativa negli ultimi 24 mesi: quanti atti per mese e quali trend emergono?",
                "Crea una timeline dei progetti PNRR: quando sono stati approvati, quali sono in corso, quali completati",
                "Confronta l'attività amministrativa del 2023 vs 2024: differenze nei settori di intervento",
                "Identifica i periodi di maggiore attività legislativa e correlali con eventi o scadenze importanti",
                "Mostra la frequenza di pubblicazione atti per trimestre negli ultimi 2 anni con un grafico temporale",
            ],

            // Compliance & Normativa (5 questions)
            'compliance_normativa' => [
                "Quali atti citano il D.Lgs 50/2016 (Codice Appalti)? Crea un elenco con riferimenti normativi specifici",
                "Identifica gli atti relativi a procedure di gara e verifica se contengono riferimenti a CIG/CUP",
                "Trova atti che menzionano normative GDPR o privacy e categorizzali per tipologia di trattamento dati",
                "Cerca atti relativi a trasparenza amministrativa e anticorruzione secondo D.Lgs 33/2013",
                "Elenca atti che citano normative ambientali (VIA, VAS, AIA) e mostra i riferimenti legislativi",
            ],

            // Anomalie & Controllo (5 questions)
            'anomalies_control' => [
                "Identifica eventuali codici CUP duplicati o anomalie nei riferimenti progettuali",
                "Trova atti con informazioni mancanti o incomplete (senza date, CUP, o descrizioni dettagliate)",
                "Identifica atti che potrebbero essere collegati ma non hanno riferimenti incrociati espliciti",
                "Cerca atti pubblicati con delay significativo rispetto alla data di adozione",
                "Analizza la consistenza delle categorizzazioni: atti simili hanno classificazioni coerenti?",
            ],

            // Sintesi & Reporting (5 questions)
            'synthesis_reporting' => [
                "Crea una sintesi esecutiva delle politiche ambientali adottate tra 2023 e 2024",
                "Genera un report sui principali interventi urbanistici: progetti, localizzazioni, stato avanzamento",
                "Riassumi l'attività del settore sociale: quali servizi, quali target, quali iniziative principali",
                "Crea un quadro sinottico dei progetti culturali approvati negli ultimi 18 mesi",
                "Genera statistiche aggregate: totale atti per settore, periodo medio tra adozione e pubblicazione",
            ],

            // Relazioni & Collegamenti (5 questions)
            'relationships_links' => [
                "Mappa tutti gli atti collegati al Piano Urbanistico Generale (PUG) e mostra le relazioni",
                "Identifica atti che modificano o integrano delibere precedenti e crea una catena temporale",
                "Trova atti correlati per tema anche se non esplicitamente collegati (stesso CUP, stessa area, stesso settore)",
                "Analizza le dipendenze tra progetti: quali atti devono essere completati prima di altri",
                "Crea un grafo delle relazioni tra atti di finanziamento e atti di esecuzione progetti",
            ],

            // Supporto Decisionale (5 questions)
            'decision_support' => [
                "Cerca precedenti amministrativi su affidamenti di servizi mensa scolastica: come sono stati gestiti?",
                "Identifica best practices utilizzate in progetti di mobilità sostenibile negli ultimi 2 anni",
                "Quali modelli contrattuali sono stati usati per appalti di manutenzione ordinaria?",
                "Analizza le modalità di partecipazione cittadina usate nei progetti di riqualificazione urbana",
                "Trova esempi di progetti simili completati: quali lezioni apprese, quali criticità superate?",
            ],

            // Power Questions (4 questions)
            'power_questions' => [
                "Crea una dashboard strategica per il Sindaco con i 10 KPI più critici della città, analisi trend e early warning systems",
                "Identifica le 3 azioni quick-win con massimo impatto politico e minimo costo, con timeline 60 giorni e piano esecutivo dettagliato",
                "Analizza tutti i progetti e crea una roadmap strategica 2024-2026 con prioritization matrix, dependencies e critical path",
                "Simula 3 scenari futuri (ottimistico, realistico, pessimistico) per il portfolio progetti e proponi strategie di adattamento per ciascuno",
            ],
        ];
    }

    /**
     * Get all strategic questions as a flat array (for random selection)
     *
     * @return array
     */
    private function getAllStrategicQuestionsFlat(): array
    {
        $library = $this->getStrategicQuestionsLibrary();
        $allQuestions = [];
        foreach ($library as $category => $questions) {
            $allQuestions = array_merge($allQuestions, $questions);
        }
        return $allQuestions;
    }
}
