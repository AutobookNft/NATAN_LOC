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
    // Get URS label - if not present, try to derive from score using UrsBadge helper
    const allowedLabels: Array<'A' | 'B' | 'C' | 'X'> = ['A', 'B', 'C', 'X'];
    const rawLabel = typeof claim.ursLabel === 'string' ? claim.ursLabel.toUpperCase() : undefined;
    let ursLabel: 'A' | 'B' | 'C' | 'X' = allowedLabels.includes(rawLabel as any)
      ? (rawLabel as 'A' | 'B' | 'C' | 'X')
      : UrsBadge.getLabelFromScore(claim.urs);

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

    // Source links - Verified sources section
    if (claim.sourceRefs && claim.sourceRefs.length > 0) {
      html += `
        <div class="mt-3 pt-2 border-t border-gray-200">
          <p class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1">
            <svg class="w-3 h-3 text-natan-green" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Fonti Verificate
          </p>
          <ul class="space-y-1.5" role="list">
            ${claim.sourceRefs.map((ref, index) => {
        // Handle internal document references (url starting with #)
        const isInternal = ref.url && ref.url.startsWith('#');
        const linkHref = isInternal ? 'javascript:void(0)' : this.escapeHtml(ref.url || '');

        // Build display text
        const titleText = this.escapeHtml(ref.title || 'Documento senza titolo');
        const pageText = ref.page ? ` <span class="text-gray-500 font-mono">p. ${ref.page}</span>` : '';
        const chunkText = ref.chunk_index !== undefined ? ` <span class="text-gray-400 font-mono text-[10px]">[chunk ${ref.chunk_index}]</span>` : '';

        // Icon for external/internal links
        const linkIcon = isInternal
          ? '<svg class="w-3 h-3 inline-block mr-1 text-natan-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'
          : '<svg class="w-3 h-3 inline-block mr-1 text-natan-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>';

        const linkClass = isInternal
          ? 'inline-flex items-start gap-1 text-xs text-natan-blue hover:text-natan-blue-dark hover:underline cursor-pointer transition-colors'
          : 'inline-flex items-start gap-1 text-xs text-natan-blue hover:text-natan-blue-dark hover:underline transition-colors';

        const ariaLabel = isInternal
          ? `Fonte ${index + 1}: ${titleText}${ref.page ? `, pagina ${ref.page}` : ''} (Documento interno)`
          : `Fonte ${index + 1}: ${titleText}${ref.page ? `, pagina ${ref.page}` : ''} (Link esterno)`;

        return `
            <li>
              <a 
                href="${linkHref}" 
                ${!isInternal ? 'target="_blank" rel="noopener noreferrer"' : ''}
                class="${linkClass}"
                aria-label="${ariaLabel}"
                title="${isInternal ? 'Documento interno: ' : 'Link esterno: '}${titleText}${ref.page ? ` (pagina ${ref.page})` : ''}"
              >
                ${linkIcon}
                <span>${titleText}${pageText}${chunkText}</span>
              </a>
            </li>
          `;
      }).join('')}
          </ul>
        </div>
      `;
    } else {
      html += `
        <div class="mt-3 pt-2 border-t border-gray-200">
          <p class="text-xs text-amber-600 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            Nessuna fonte disponibile per questo claim
          </p>
        </div>
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
    // Use NATAN semantic colors for claim text
    const classes: Record<string, string> = {
      'A': 'text-emerald-900 font-medium',
      'B': 'text-blue-900 font-medium',
      'C': 'text-amber-900 font-medium',
      'X': 'text-red-900 font-medium',
    };
    return classes[ursLabel] || 'text-gray-900';
  }

  private static escapeHtml(text: string): string {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}




