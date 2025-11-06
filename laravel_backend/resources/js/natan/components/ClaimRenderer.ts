/**
 * Claim Renderer Component
 * Renders verified claims with URS badges and source links
 */

import type { Claim } from '../types';
import { UrsBadge } from './UrsBadge';

export class ClaimRenderer {
  static renderClaims(claims: Claim[], verificationStatus?: string): string {
    if (!claims || claims.length === 0) {
      return '<p class="text-gray-500">Nessun claim verificato disponibile.</p>';
    }

    return claims.map(claim => this.renderClaim(claim, verificationStatus)).join('');
  }

  static renderClaim(claim: Claim, verificationStatus?: string): string {
    // Get URS label - if not present, try to derive from score
    let ursLabel: 'A' | 'B' | 'C' | 'X' = claim.ursLabel;
    if (!ursLabel) {
      const score = claim.urs ?? 0;
      if (score >= 0.85) ursLabel = 'A';
      else if (score >= 0.70) ursLabel = 'B';
      else if (score >= 0.50) ursLabel = 'C';
      else ursLabel = 'X';
    }
    
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
          ${claim.sourceRefs.map((ref) => {
            // Handle internal document references (url starting with #doc-)
            const isInternal = ref.url && ref.url.startsWith('#doc-');
            let linkHref = this.escapeHtml(ref.url || '#');
            let documentId: string = '';
            let attributes: string = '';
            
            // Convert #doc-{document_id} to /natan/documents/view/{document_id}
            if (isInternal && ref.url) {
              documentId = ref.url.replace('#doc-', '');
              // Usa encodeURIComponent per gestire caratteri speciali nel document_id
              linkHref = `/natan/documents/view/${encodeURIComponent(documentId)}`;
              attributes = `data-document-id="${this.escapeHtml(documentId)}" title="Visualizza documento: ${this.escapeHtml(ref.title || '')}"`;
              console.log('[ClaimRenderer] Internal document link:', { original: ref.url, documentId, linkHref });
            } else {
              attributes = 'target="_blank" rel="noopener noreferrer"';
            }
            
            const linkClass = 'block text-xs text-blue-600 hover:underline cursor-pointer';
            const title = this.escapeHtml(ref.title || 'Senza titolo');
            const pageInfo = ref.page ? ` (p. ${ref.page})` : '';
            const chunkInfo = ref.chunk_index !== undefined ? ` [chunk ${ref.chunk_index}]` : '';
            
            return `
            <a 
              href="${linkHref}" 
              ${attributes}
              class="${linkClass}"
            >
              → ${title}${pageInfo}${chunkInfo}
            </a>
          `;
          }).join('')}
        </div>
      `;
    } else {
      // Per query dirette (direct_query), mostra messaggio positivo invece di errore
      if (verificationStatus === 'direct_query' || verificationStatus === 'DIRECT_QUERY') {
        html += `
          <p class="mt-2 text-xs text-green-600 font-semibold">✓ Risposta diretta da database MongoDB (query diretta, massima affidabilità)</p>
        `;
      } else {
        html += `
          <p class="mt-2 text-xs text-red-600">Nessuna fonte disponibile per questo claim.</p>
        `;
      }
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




