/**
 * Message Component
 * Renders a single chat message (user or assistant)
 */

import type { Message } from '../types';
import { ClaimRenderer } from './ClaimRenderer';

export class MessageComponent {
  static render(message: Message): HTMLElement {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${message.role} flex ${message.role === 'user' ? 'justify-end' : 'justify-start'}`;
    messageDiv.setAttribute('data-message-id', message.id);
    messageDiv.setAttribute('role', 'article');

    const bubble = document.createElement('div');
    bubble.className = `max-w-3xl rounded-lg px-4 py-3 ${
      message.role === 'user'
        ? 'bg-blue-600 text-white'
        : 'bg-gray-100 text-gray-900'
    }`;

    // Message content
    const contentDiv = document.createElement('div');
    contentDiv.className = 'message-content';
    contentDiv.textContent = message.content;
    bubble.appendChild(contentDiv);

    // Claims (only for assistant messages)
    if (message.role === 'assistant' && message.claims?.length) {
      const claimsSection = document.createElement('div');
      claimsSection.className = 'mt-4 space-y-3';
      claimsSection.innerHTML = ClaimRenderer.renderClaims(message.claims);
      bubble.appendChild(claimsSection);
    }

    // Blocked claims
    if (message.role === 'assistant' && message.blockedClaims?.length) {
      const blockedSection = document.createElement('div');
      blockedSection.className = 'mt-4 p-4 bg-red-50 border border-red-200 rounded-lg';
      blockedSection.innerHTML = `
        <p class="font-semibold text-red-800">Claim Bloccati</p>
        <p class="text-sm text-red-600 mt-1">
          I seguenti claim sono stati bloccati perché non soddisfano i requisiti di affidabilità minimi (URS < 0.5).
        </p>
        <ul class="mt-2 space-y-1 text-sm text-red-700">
          ${message.blockedClaims.map(claim => 
            `<li>• ${this.escapeHtml(claim.text)}</li>`
          ).join('')}
        </ul>
      `;
      bubble.appendChild(blockedSection);
    }

    // Sources
    if (message.role === 'assistant' && message.sources?.length) {
      const sourcesSection = document.createElement('div');
      sourcesSection.className = 'mt-4 pt-4 border-t border-gray-300';
      sourcesSection.innerHTML = `
        <p class="text-xs font-semibold text-gray-600 mb-2">Fonti:</p>
        <div class="space-y-1">
          ${message.sources.map(source => `
            <a 
              href="${this.escapeHtml(source.url)}" 
              target="_blank" 
              rel="noopener noreferrer" 
              class="block text-xs text-blue-600 hover:underline"
            >
              → ${this.escapeHtml(source.title)}
            </a>
          `).join('')}
        </div>
      `;
      bubble.appendChild(sourcesSection);
    }

    // Metadata (URS, verification status)
    if (message.role === 'assistant' && message.avgUrs !== undefined) {
      const metadataDiv = document.createElement('div');
      metadataDiv.className = 'mt-3 pt-3 border-t border-gray-300 text-xs text-gray-500';
      metadataDiv.innerHTML = `
        <span>URS Medio: ${message.avgUrs.toFixed(2)}</span>
        ${message.verificationStatus ? 
          `<span class="ml-3">Status: ${message.verificationStatus}</span>` : 
          ''
        }
      `;
      bubble.appendChild(metadataDiv);
    }

    messageDiv.appendChild(bubble);
    return messageDiv;
  }

  private static escapeHtml(text: string): string {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}

