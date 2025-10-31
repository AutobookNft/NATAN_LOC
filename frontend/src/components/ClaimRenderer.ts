/**
 * Claim Renderer Component
 * Renders verified claims with URS badges and source links
 */

import type { Claim } from '../types';
import { UrsBadge } from './UrsBadge';

export class ClaimRenderer {
  static renderClaims(claims: Claim[]): string {
    if (!claims || claims.length === 0) {
      return '<p class="text-gray-500">Nessun claim verificato disponibile.</p>';
    }

    return claims.map(claim => this.renderClaim(claim)).join('');
  }

  static renderClaim(claim: Claim): string {
    const ursLabel = claim.ursLabel || 'UNKNOWN';
    const containerClass = this.getContainerClass(ursLabel);
    const textClass = this.getTextClass(ursLabel);

    let html = `
      <div class="${containerClass}">
        <div class="flex items-start gap-2 mb-2">
          ${UrsBadge.render(ursLabel, claim.urs ?? null)}
          ${claim.isInference ?
        '<span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-orange-100 text-orange-800">[Deduzione]</span>' :
        ''
      }
        </div>
        <p class="${textClass}">${this.escapeHtml(claim.text)}</p>
    `;

    // Source links
    if (claim.sourceRefs && claim.sourceRefs.length > 0) {
      html += `
        <div class="mt-3 space-y-1">
          <p class="text-xs font-semibold text-gray-600">Fonti:</p>
          ${claim.sourceRefs.map(ref => `
            <a 
              href="${this.escapeHtml(ref.url)}" 
              target="_blank" 
              rel="noopener noreferrer" 
              class="block text-xs text-blue-600 hover:underline"
            >
              → ${this.escapeHtml(ref.title)}${ref.page ? ` (p. ${ref.page})` : ''}
            </a>
          `).join('')}
        </div>
      `;
    } else {
      html += `
        <p class="mt-2 text-xs text-red-600">Nessuna fonte disponibile per questo claim.</p>
      `;
    }

    // URS breakdown (collapsible)
    if (claim.ursBreakdown) {
      html += this.renderUrsBreakdown(claim.ursBreakdown);
    }

    html += '</div>';

    return html;
  }

  private static renderUrsBreakdown(breakdown: any): string {
    return `
      <details class="mt-2">
        <summary class="text-xs text-gray-600 cursor-pointer hover:text-gray-800">
          Dettagli URS
        </summary>
        <div class="mt-2 text-xs space-y-1">
          ${Object.entries(breakdown)
        .filter(([key]) => key !== 'total')
        .map(([key, value]) => {
          const numValue = typeof value === 'number' && !isNaN(value)
            ? value.toFixed(2)
            : (value ?? 'N/A');
          return `
              <div class="flex justify-between">
                <span>${this.formatKey(key)}:</span>
                <span class="font-semibold">${numValue}</span>
              </div>
            `;
        }).join('')}
        </div>
      </details>
    `;
  }

  private static formatKey(key: string): string {
    return key
      .replace(/_/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }

  private static getContainerClass(ursLabel: string): string {
    const classes: Record<string, string> = {
      'A': 'claim-container-a',
      'B': 'claim-container-b',
      'C': 'claim-container-c',
      'X': 'claim-container-x',
    };
    return classes[ursLabel] || 'claim-container';
  }

  private static getTextClass(ursLabel: string): string {
    const classes: Record<string, string> = {
      'A': 'text-green-900',
      'B': 'text-blue-900',
      'C': 'text-yellow-900',
      'X': 'text-red-900',
    };
    return classes[ursLabel] || 'text-gray-900';
  }

  private static escapeHtml(text: string): string {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}




