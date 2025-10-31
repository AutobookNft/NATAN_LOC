/**
 * URS Badge Component
 * Renders Ultra Reliability Score badge with label and color
 */

export class UrsBadge {
    static render(label: 'A' | 'B' | 'C' | 'X', score?: number): string {
        const badgeClass = `urs-badge-${label.toLowerCase()}`;
        const labelText = this.getLabelText(label);

        let html = `<span class="${badgeClass}" aria-label="${labelText}">`;
        html += `URS ${label}`;
        if (score !== undefined) {
            html += ` (${score.toFixed(2)})`;
        }
        html += '</span>';

        return html;
    }

    private static getLabelText(label: string): string {
        const labels: Record<string, string> = {
            'A': 'Affidabilità Alta',
            'B': 'Affidabilità Media-Alta',
            'C': 'Affidabilità Media',
            'X': 'Affidabilità Bassa',
        };
        return labels[label] || 'Affidabilità Sconosciuta';
    }
}

