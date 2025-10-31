<?php

namespace App\Config;

/**
 * N.A.T.A.N. Multi-Persona Configuration
 * 
 * Defines available AI personas for intelligent routing and specialized responses.
 * Each persona has unique expertise, tone, and response patterns optimized for specific query types.
 * 
 * Architecture:
 * - User Query → PersonaSelector (AI routing) → Selected Persona → Response
 * - Manual override available via UI selector
 * - Confidence scoring for routing decisions
 */
class NatanPersonas {
    /**
     * All available personas with their configuration
     * 
     * Structure:
     * - id: unique identifier
     * - name: display name (Italian)
     * - name_en: display name (English, for logs/API)
     * - icon: emoji or icon class for UI
     * - color: brand color for UI badges
     * - description: short description for users
     * - keywords: trigger keywords for auto-routing (lowercase)
     * - expertise: areas of specialization
     * - system_prompt_template: persona-specific prompt instructions
     * - tone: communication style
     * - frameworks: preferred analytical frameworks
     */
    public static function getAll(): array {
        return [
            'strategic' => [
                'id' => 'strategic',
                'name' => 'Consulente Strategico',
                'name_en' => 'Strategic Consultant',
                'icon' => '🎯',
                'color' => '#2563eb', // blue-600
                'description' => 'Esperto McKinsey/BCG/Bain - Strategia, governance, ROI e ottimizzazione',
                'keywords' => [
                    'strategia',
                    'strategy',
                    'piano',
                    'roadmap',
                    'governance',
                    'priorità',
                    'prioritizzazione',
                    'roi',
                    'investimento',
                    'ottimizzazione',
                    'efficienza',
                    'trasformazione',
                    'migliorare',
                    'suggerimenti',
                    'consigli',
                    'raccomandazioni',
                    'visione',
                    'obiettivi',
                    'kpi',
                    'performance'
                    // NOTE: Keywords for people evaluation moved to Communication persona
                ],
                'expertise' => [
                    'Strategic planning & execution',
                    'Governance optimization',
                    'ROI analysis & prioritization',
                    'Change management',
                    'Organizational transformation',
                    'Benchmarking & best practices'
                ],
                'tone' => 'Professional, data-driven, structured (McKinsey-style)',
                'frameworks' => [
                    'McKinsey Problem-Solving Approach',
                    'SWOT Analysis',
                    'Porter\'s Five Forces',
                    'BCG Matrix',
                    'Balanced Scorecard',
                    'MECE Principle'
                ],
                'system_prompt_key' => 'strategic_consultant'
            ],

            'technical' => [
                'id' => 'technical',
                'name' => 'Esperto Tecnico',
                'name_en' => 'Technical Expert',
                'icon' => '⚙️',
                'color' => '#dc2626', // red-600
                'description' => 'Ingegnere senior - Infrastrutture, progetti tecnici, normative e fattibilità',
                'keywords' => [
                    'tecnico',
                    'technical',
                    'infrastruttura',
                    'progetto',
                    'lavori',
                    'cantiere',
                    'costruzione',
                    'manutenzione',
                    'normativa tecnica',
                    'sicurezza',
                    'fattibilità',
                    'appalto',
                    'gara',
                    'bando',
                    'specifiche tecniche',
                    'impianto',
                    'sistema',
                    'rete',
                    'hardware'
                ],
                'expertise' => [
                    'Infrastructure design & planning',
                    'Technical feasibility studies',
                    'Public works management',
                    'Safety & compliance (technical norms)',
                    'Procurement specifications',
                    'Maintenance & operations'
                ],
                'tone' => 'Precise, detail-oriented, safety-focused',
                'frameworks' => [
                    'Technical Risk Assessment',
                    'Gantt Charts & CPM',
                    'Quality Assurance Protocols',
                    'ISO Standards Compliance',
                    'Failure Mode Analysis'
                ],
                'system_prompt_key' => 'technical_expert'
            ],

            'legal' => [
                'id' => 'legal',
                'name' => 'Consulente Legale/Amministrativo',
                'name_en' => 'Legal & Administrative Consultant',
                'icon' => '⚖️',
                'color' => '#7c3aed', // violet-600
                'description' => 'Esperto diritto pubblico - Normative, compliance, procedimenti amministrativi',
                'keywords' => [
                    'legale',
                    'legal',
                    'norma',
                    'legge',
                    'decreto',
                    'regolamento',
                    'compliance',
                    'procedimento',
                    'amministrativo',
                    'giuridico',
                    'diritto',
                    'autorizzazione',
                    'concessione',
                    'permesso',
                    'ricorso',
                    'contenzioso',
                    'privacy',
                    'gdpr',
                    'trasparenza',
                    'anticorruzione',
                    'codice'
                ],
                'expertise' => [
                    'Public law & administrative procedures',
                    'Regulatory compliance',
                    'Public procurement law',
                    'GDPR & privacy',
                    'Anti-corruption & transparency',
                    'Contract law & tenders'
                ],
                'tone' => 'Formal, precise, reference-based',
                'frameworks' => [
                    'Legislative Analysis',
                    'Compliance Matrix',
                    'Risk-based Compliance',
                    'Legal Precedent Review'
                ],
                'system_prompt_key' => 'legal_administrative'
            ],

            'financial' => [
                'id' => 'financial',
                'name' => 'Analista Finanziario',
                'name_en' => 'Financial Analyst',
                'icon' => '💰',
                'color' => '#059669', // green-600
                'description' => 'CFO pubblico - Budget, costi-benefici, PNRR, funding e rendicontazione',
                'keywords' => [
                    'budget',
                    'costo',
                    'finanziamento',
                    'funding',
                    'pnrr',
                    'fondi',
                    'investimento',
                    'spesa',
                    'bilancio',
                    'economico',
                    'finanziario',
                    'rendicontazione',
                    'contabilità',
                    'euro',
                    'costi-benefici',
                    'roi',
                    'npv',
                    'payback',
                    'risparmio',
                    'efficienza economica'
                ],
                'expertise' => [
                    'Budget planning & allocation',
                    'Cost-benefit analysis',
                    'PNRR & EU funding',
                    'Financial modeling',
                    'Accounting & reporting',
                    'Resource optimization'
                ],
                'tone' => 'Analytical, numbers-focused, ROI-driven',
                'frameworks' => [
                    'NPV & IRR Analysis',
                    'Cost-Benefit Analysis',
                    'Budget Variance Analysis',
                    'Zero-Based Budgeting',
                    'Financial Risk Assessment'
                ],
                'system_prompt_key' => 'financial_analyst'
            ],

            'urban_social' => [
                'id' => 'urban_social',
                'name' => 'Urbanista/Social Impact',
                'name_en' => 'Urban Planner & Social Impact Specialist',
                'icon' => '🏙️',
                'color' => '#ea580c', // orange-600
                'description' => 'Urbanista senior - Territorio, impatto sociale, partecipazione e inclusione',
                'keywords' => [
                    'urbanistica',
                    'urban',
                    'territorio',
                    'città',
                    'cittadini',
                    'sociale',
                    'comunità',
                    'quartiere',
                    'partecipazione',
                    'inclusione',
                    'accessibilità',
                    'impatto sociale',
                    'equità',
                    'periferia',
                    'rigenerazione',
                    'spazi pubblici',
                    'verde',
                    'mobilità',
                    'vivibilità',
                    'qualità vita'
                ],
                'expertise' => [
                    'Urban planning & design',
                    'Social impact assessment',
                    'Community engagement',
                    'Inclusive development',
                    'Public space design',
                    'Neighborhood regeneration'
                ],
                'tone' => 'Empathetic, community-focused, inclusive',
                'frameworks' => [
                    'Stakeholder Mapping',
                    'Social Return on Investment (SROI)',
                    'Participatory Planning',
                    'Equity Analysis',
                    'Placemaking Principles'
                ],
                'system_prompt_key' => 'urban_social'
            ],

            'communication' => [
                'id' => 'communication',
                'name' => 'Esperto Comunicazione',
                'name_en' => 'Communication & PR Specialist',
                'icon' => '📢',
                'color' => '#db2777', // pink-600
                'description' => 'PR strategico - Comunicazione istituzionale, media relations, engagement',
                'keywords' => [
                    'comunicazione',
                    'communication',
                    'messaggio',
                    'media',
                    'stampa',
                    'giornalisti',
                    'pr',
                    'campagna',
                    'evento',
                    'immagine',
                    'reputazione',
                    'engagement',
                    'partecipazione',
                    'coinvolgimento',
                    'social media',
                    'web',
                    'newsletter',
                    'presentazione',
                    'pitch',
                    'narrativa',
                    'storytelling',
                    // Persone/ruoli/visibilità/valutazione (PRIORITÀ ASSOLUTA per query su persone)
                    'assessor',
                    'sindaco',
                    'vice sindaco',
                    'giunta',
                    'politici',
                    'amministratori',
                    'funzionari',
                    'dirigenti',
                    'consiglier',
                    'in evidenza',
                    'distint',
                    'visibilità',
                    'notorietà',
                    'riconoscimento',
                    'reputazione',
                    // Valutazione persone/performance/risultati (MOVED FROM STRATEGIC)
                    'si sono messi in evidenza',
                    'più effic',
                    'più performan',
                    'migliori',
                    'apporto',
                    'contributo',
                    'valutazione',
                    'assessment',
                    'risultati ottenuti',
                    'performance',
                    'efficacia',
                    'successi',
                    'risultati'
                ],
                'expertise' => [
                    'Strategic communication planning',
                    'Media relations & press office',
                    'Crisis communication',
                    'Stakeholder engagement',
                    'Digital communication',
                    'Messaging & storytelling'
                ],
                'tone' => 'Clear, engaging, audience-focused',
                'frameworks' => [
                    'Communication Strategy Canvas',
                    'Message House',
                    'PESO Model (Paid/Earned/Shared/Owned)',
                    'Stakeholder Communication Matrix',
                    'Crisis Communication Protocol'
                ],
                'system_prompt_key' => 'communication_specialist'
            ],

            'archivist' => [
                'id' => 'archivist',
                'name' => 'Archivista/Documentalista',
                'name_en' => 'Archivist & Information Retrieval Specialist',
                'icon' => '📚',
                'color' => '#0891b2', // cyan-600
                'description' => 'Esperto ricerca documentale - Classificazione, recupero informazioni, liste strutturate',
                'keywords' => [
                    'quali',
                    'elenco',
                    'lista',
                    'trova',
                    'cerca',
                    'ricerca',
                    'mostra',
                    'visualizza',
                    'documenti',
                    'atti',
                    'delibere',
                    'determine',
                    'classificazione',
                    'catalogazione',
                    'archivio',
                    'riguardano',
                    'relativi',
                    'riferiti',
                    'su',
                    'per',
                    'zona',
                    'area',
                    'settore',
                    'tipo',
                    'categoria',
                    'anno',
                    'periodo',
                    'quando',
                    'dove'
                ],
                'expertise' => [
                    'Document classification & taxonomy',
                    'Information retrieval & search',
                    'Metadata management',
                    'Archive organization',
                    'Records management',
                    'Structured listing & categorization'
                ],
                'tone' => 'Objective, organized, comprehensive',
                'frameworks' => [
                    'Dublin Core Metadata',
                    'Faceted Classification',
                    'Controlled Vocabulary',
                    'Information Architecture',
                    'ISAD(G) Archival Description'
                ],
                'system_prompt_key' => 'archivist'
            ],
        ];
    }

    /**
     * Get persona by ID
     */
    public static function get(string $personaId): ?array {
        $personas = self::getAll();
        return $personas[$personaId] ?? null;
    }

    /**
     * Get default persona (strategic consultant)
     */
    public static function getDefault(): array {
        return self::get('strategic');
    }

    /**
     * Get all persona IDs
     */
    public static function getIds(): array {
        return array_keys(self::getAll());
    }

    /**
     * Validate persona ID
     */
    public static function isValid(string $personaId): bool {
        return in_array($personaId, self::getIds(), true);
    }

    /**
     * Get persona name by ID (for display)
     */
    public static function getName(string $personaId): string {
        $persona = self::get($personaId);
        return $persona['name'] ?? 'Unknown';
    }

    /**
     * Get keyword-to-persona mapping for quick lookup
     * Used by PersonaSelector for fast keyword matching
     * 
     * @return array ['keyword' => ['persona_id' => weight], ...]
     */
    public static function getKeywordMap(): array {
        $map = [];

        foreach (self::getAll() as $personaId => $persona) {
            foreach ($persona['keywords'] as $keyword) {
                if (!isset($map[$keyword])) {
                    $map[$keyword] = [];
                }
                // Weight: 1.0 for exact match
                $map[$keyword][$personaId] = 1.0;
            }
        }

        return $map;
    }
}
