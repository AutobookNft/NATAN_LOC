/**
 * Message Component
 * Renders a single chat message (user or assistant)
 */

import type { Message } from '../types';
import { ClaimRenderer } from './ClaimRenderer';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

export class MessageComponent {
  static render(message: Message): HTMLElement {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${message.role} flex ${message.role === 'user' ? 'justify-end' : 'justify-start'}`;
    messageDiv.setAttribute('data-message-id', message.id);
    messageDiv.setAttribute('role', 'article');

    const bubble = document.createElement('div');
    // Mobile-first: full width on mobile, max-w-3xl on desktop
    bubble.className = `w-full sm:max-w-3xl rounded-xl px-3 sm:px-4 py-2 sm:py-3 ${message.role === 'user'
      ? 'bg-natan-blue text-white ml-auto'
      : 'bg-natan-gray-100 text-natan-gray-900 border border-natan-gray-300'
      }`;

    // Message content (main natural language answer)
    if (message.content) {
      const contentDiv = document.createElement('div');
      // Use Tailwind Typography (prose) for beautiful markdown rendering
      contentDiv.className = 'message-content prose prose-sm prose-headings:font-bold prose-headings:text-gray-900 prose-headings:mt-6 prose-headings:mb-3 prose-h1:text-2xl prose-h2:text-xl prose-h3:text-lg prose-p:text-gray-700 prose-p:mb-4 prose-strong:text-gray-900 prose-strong:font-semibold prose-ul:list-disc prose-ul:ml-6 prose-ol:list-decimal prose-ol:ml-6 prose-li:mb-2 prose-a:text-blue-600 prose-a:underline hover:prose-a:text-blue-800 max-w-none';
      
      // Render markdown to HTML and sanitize for XSS protection
      const htmlContent = marked.parse(message.content, {
        breaks: true, // Convert line breaks to <br>
        gfm: true, // GitHub Flavored Markdown
      }) as string;
      
      // Sanitize HTML to prevent XSS attacks
      const sanitizedContent = DOMPurify.sanitize(htmlContent, {
        ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'a', 'blockquote', 'code', 'pre', 'hr'],
        ALLOWED_ATTR: ['href', 'target', 'rel', 'class'],
      });
      
      contentDiv.innerHTML = sanitizedContent;
      bubble.appendChild(contentDiv);
    }

    // Claims (verified with sources - shown as "proof" below answer)
    if (message.role === 'assistant' && message.claims?.length) {
      const claimsSection = document.createElement('div');
      claimsSection.className = 'mt-6 pt-4 border-t border-gray-300';
      claimsSection.innerHTML = `
        <p class="text-xs font-semibold text-gray-700 mb-3">Affermazioni verificate con fonti accreditate:</p>
        ${ClaimRenderer.renderClaims(message.claims)}
      `;
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
    if (message.role === 'assistant' && message.avgUrs !== undefined && message.avgUrs !== null) {
      const metadataDiv = document.createElement('div');
      metadataDiv.className = 'mt-3 pt-3 border-t border-gray-300 text-xs text-gray-500';
      metadataDiv.innerHTML = `
        <span>URS Medio: ${typeof message.avgUrs === 'number' ? message.avgUrs.toFixed(2) : 'N/A'}</span>
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




