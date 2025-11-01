/**
 * URS Badge Component
 * Renders Ultra Reliability Score badge with NATAN color palette and semantic labels
 * 
 * URS Scores:
 * - A: 0.85-1.0 (Alta Affidabilità) - Emerald-500 (#10b981)
 * - B: 0.70-0.84 (Media-Alta) - Blue-500 (#3b82f6)
 * - C: 0.50-0.69 (Media) - Amber-500 (#f59e0b)
 * - X: 0.0-0.49 (Bassa/Bloccato) - Red-500 (#ef4444)
 */

export class UrsBadge {
    static render(label: 'A' | 'B' | 'C' | 'X' | string, score?: number | null): string {
        const normalizedLabel = label.toUpperCase();
        const badgeClass = `urs-badge-${normalizedLabel.toLowerCase()}`;
        const labelText = this.getLabelText(normalizedLabel);
        const scoreText = this.formatScore(score);

        // Use semantic HTML with proper ARIA attributes
        let html = `<span class="${badgeClass}" role="status" aria-label="${labelText}${scoreText ? ` - Score: ${score}` : ''}" title="${labelText}${scoreText ? ` (${score})` : ''}">`;
        
        // URS prefix + Label
        html += `<span class="font-mono font-bold">URS ${normalizedLabel}</span>`;
        
        // Score if available
        if (scoreText) {
            html += ` <span class="font-mono text-xs opacity-90">${scoreText}</span>`;
        }
        
        html += '</span>';

        return html;
    }

    private static formatScore(score?: number | null): string {
        if (score === undefined || score === null || typeof score !== 'number' || isNaN(score)) {
            return '';
        }
        return `(${score.toFixed(2)})`;
    }

    private static getLabelText(label: string): string {
        const labels: Record<string, string> = {
            'A': 'Affidabilità Alta',
            'B': 'Affidabilità Media-Alta',
            'C': 'Affidabilità Media',
            'X': 'Affidabilità Bassa o Bloccata',
        };
        return labels[label] || 'Affidabilità Sconosciuta';
    }

    /**
     * Get URS label from numeric score
     */
    static getLabelFromScore(score: number | null | undefined): 'A' | 'B' | 'C' | 'X' {
        if (score === null || score === undefined || isNaN(score)) {
            return 'X';
        }
        if (score >= 0.85) return 'A';
        if (score >= 0.70) return 'B';
        if (score >= 0.50) return 'C';
        return 'X';
    }
}




