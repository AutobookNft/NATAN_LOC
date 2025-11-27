/**
 * URS Badge Component
 * Displays Ultra Reliability Score (0-100) with color coding
 * Green: >90 (Excellent), Yellow: 70-90 (Good), Red: <70 (Poor)
 */

export class UrsBadge {
  /**
   * Render URS badge with color coding
   * 
   * @param ursScore Score 0-100
   * @param explanation Optional explanation text
   * @returns HTMLElement
   */
  static render(ursScore: number | undefined | null, explanation?: string): HTMLElement {
    const badgeContainer = document.createElement('div');
    badgeContainer.className = 'urs-badge-container flex flex-col gap-2';

    if (ursScore === undefined || ursScore === null || isNaN(ursScore)) {
      // No URS score available
      return badgeContainer;
    }

    // Determine color based on score
    let colorClass: string;
    let label: string;
    let bgColor: string;
    let textColor: string;
    let borderColor: string;

    if (ursScore >= 90) {
      // Excellent (Green)
      colorClass = 'excellent';
      label = 'Eccellente';
      bgColor = 'bg-emerald-50';
      textColor = 'text-emerald-700';
      borderColor = 'border-emerald-300';
    } else if (ursScore >= 70) {
      // Good (Yellow)
      colorClass = 'good';
      label = 'Buono';
      bgColor = 'bg-yellow-50';
      textColor = 'text-yellow-700';
      borderColor = 'border-yellow-300';
    } else {
      // Poor (Red)
      colorClass = 'poor';
      label = 'Insufficiente';
      bgColor = 'bg-red-50';
      textColor = 'text-red-700';
      borderColor = 'border-red-300';
    }

    // Badge element
    const badge = document.createElement('div');
    badge.className = `urs-badge flex items-center gap-2 px-3 py-1.5 rounded-sm border ${bgColor} ${textColor} ${borderColor}`;
    badge.setAttribute('role', 'status');
    badge.setAttribute('aria-label', `Ultra Reliability Score: ${ursScore.toFixed(1)}/100 (${label})`);

    // Score value
    const scoreSpan = document.createElement('span');
    scoreSpan.className = 'font-mono font-bold text-sm';
    scoreSpan.textContent = ursScore.toFixed(1);
    badge.appendChild(scoreSpan);

    // Label
    const labelSpan = document.createElement('span');
    labelSpan.className = 'text-xs font-semibold uppercase tracking-wide';
    labelSpan.textContent = label;
    badge.appendChild(labelSpan);

    // URS text
    const ursText = document.createElement('span');
    ursText.className = 'text-xs ml-auto';
    ursText.textContent = 'URS';
    badge.appendChild(ursText);

    badgeContainer.appendChild(badge);

    // Explanation tooltip (if provided)
    if (explanation) {
      const explanationDiv = document.createElement('div');
      explanationDiv.className = 'urs-explanation text-xs text-slate-600 leading-relaxed';
      explanationDiv.setAttribute('role', 'tooltip');
      explanationDiv.textContent = explanation;
      badgeContainer.appendChild(explanationDiv);
    }

    return badgeContainer;
  }

  /**
   * Get color class for URS score (for use in other components)
   */
  static getColorClass(ursScore: number | undefined | null): string {
    if (ursScore === undefined || ursScore === null || isNaN(ursScore)) {
      return 'text-slate-500';
    }
    if (ursScore >= 90) {
      return 'text-emerald-700';
    } else if (ursScore >= 70) {
      return 'text-yellow-700';
    } else {
      return 'text-red-700';
    }
  }
}
