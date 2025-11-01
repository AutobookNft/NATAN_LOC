/**
 * Message Component
 * Renders a single chat message (user or assistant)
 * Mobile-first with optimized markdown rendering
 */

import type { Message } from '../types';
import { ClaimRenderer } from './ClaimRenderer';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

export class MessageComponent {
  static render(message: Message): HTMLElement {
    const messageDiv = document.createElement('div');
    // Mobile-first: spacing ottimizzato
    messageDiv.className = `message message-${message.role} flex ${message.role === 'user' ? 'justify-end' : 'justify-start'} mb-3 sm:mb-4`;
    messageDiv.setAttribute('data-message-id', message.id);
    messageDiv.setAttribute('role', 'article');
    messageDiv.setAttribute('aria-label', message.role === 'user' ? 'Messaggio utente' : 'Risposta assistente');

    const bubble = document.createElement('div');
    // Mobile-first: full width on mobile, max-w-3xl on desktop
    // Animazione subtle su mobile per feedback visivo
    bubble.className = `w-full sm:max-w-3xl rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 shadow-sm transition-shadow ${message.role === 'user'
      ? 'bg-natan-blue text-white ml-auto hover:shadow-md'
      : 'bg-white text-natan-gray-900 border border-natan-gray-300 hover:shadow-md'
      }`;

    // Message content (main natural language answer)
    if (message.content) {
      const contentDiv = document.createElement('div');
      
      // Mobile-first prose classes con colori NATAN
      const proseClasses = message.role === 'user'
        ? 'prose prose-sm prose-invert max-w-none prose-headings:text-white prose-headings:font-bold prose-headings:mt-4 prose-headings:mb-2 prose-h1:text-xl sm:prose-h1:text-2xl prose-h2:text-lg sm:prose-h2:text-xl prose-h3:text-base sm:prose-h3:text-lg prose-p:text-white/95 prose-p:mb-3 prose-p:leading-relaxed prose-strong:text-white prose-strong:font-semibold prose-ul:list-disc prose-ul:ml-4 sm:prose-ul:ml-6 prose-ol:list-decimal prose-ol:ml-4 sm:prose-ol:ml-6 prose-li:mb-1.5 prose-li:leading-relaxed prose-a:text-white/90 prose-a:underline hover:prose-a:text-white prose-code:text-white/90 prose-code:bg-white/20 prose-code:px-1 prose-code:py-0.5 prose-code:rounded prose-pre:bg-white/10 prose-pre:border prose-pre:border-white/20 prose-blockquote:border-l-white/50 prose-blockquote:text-white/90'
        : 'prose prose-sm max-w-none prose-headings:font-institutional prose-headings:font-bold prose-headings:text-natan-blue-dark prose-headings:mt-4 prose-headings:mb-2 prose-h1:text-xl sm:prose-h1:text-2xl prose-h2:text-lg sm:prose-h2:text-xl prose-h3:text-base sm:prose-h3:text-lg prose-p:text-natan-gray-700 prose-p:mb-3 prose-p:leading-relaxed prose-strong:text-natan-gray-900 prose-strong:font-semibold prose-ul:list-disc prose-ul:ml-4 sm:prose-ul:ml-6 prose-ol:list-decimal prose-ol:ml-4 sm:prose-ol:ml-6 prose-li:mb-1.5 prose-li:leading-relaxed prose-a:text-natan-blue prose-a:underline hover:prose-a:text-natan-blue-dark prose-code:text-natan-blue-dark prose-code:bg-natan-gray-100 prose-code:px-1 prose-code:py-0.5 prose-code:rounded prose-pre:bg-natan-gray-100 prose-pre:border prose-pre:border-natan-gray-300 prose-blockquote:border-l-natan-blue prose-blockquote:text-natan-gray-600';
      
      contentDiv.className = `message-content ${proseClasses}`;
      
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
      claimsSection.className = 'mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-natan-gray-300';
      claimsSection.setAttribute('aria-label', 'Affermazioni verificate');
      
      const claimsHeader = document.createElement('p');
      claimsHeader.className = 'text-xs sm:text-sm font-institutional font-semibold text-natan-blue-dark mb-2 sm:mb-3';
      claimsHeader.textContent = 'Affermazioni verificate con fonti accreditate:';
      claimsSection.appendChild(claimsHeader);
      
      const claimsContainer = document.createElement('div');
      claimsContainer.innerHTML = ClaimRenderer.renderClaims(message.claims);
      claimsSection.appendChild(claimsContainer);
      
      bubble.appendChild(claimsSection);
    }

    // Blocked claims (mobile-first styling)
    if (message.role === 'assistant' && message.blockedClaims?.length) {
      const blockedSection = document.createElement('div');
      blockedSection.className = 'mt-3 sm:mt-4 p-3 sm:p-4 bg-red-50 border border-red-200 rounded-lg';
      blockedSection.setAttribute('role', 'alert');
      blockedSection.setAttribute('aria-label', 'Claim bloccati');
      
      const header = document.createElement('p');
      header.className = 'font-institutional font-semibold text-red-800 text-sm sm:text-base';
      header.textContent = 'Claim Bloccati';
      blockedSection.appendChild(header);
      
      const description = document.createElement('p');
      description.className = 'text-xs sm:text-sm text-red-600 mt-1.5 sm:mt-2 leading-relaxed';
      description.textContent = 'I seguenti claim sono stati bloccati perché non soddisfano i requisiti di affidabilità minimi (URS < 0.5).';
      blockedSection.appendChild(description);
      
      const list = document.createElement('ul');
      list.className = 'mt-2 sm:mt-3 space-y-1.5 text-xs sm:text-sm text-red-700 list-disc list-inside';
      message.blockedClaims.forEach(claim => {
        const li = document.createElement('li');
        li.textContent = this.escapeHtml(claim.text || '');
        list.appendChild(li);
      });
      blockedSection.appendChild(list);
      
      bubble.appendChild(blockedSection);
    }

    // Sources (mobile-first with better link styling)
    if (message.role === 'assistant' && message.sources?.length) {
      const sourcesSection = document.createElement('div');
      sourcesSection.className = 'mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-natan-gray-300';
      sourcesSection.setAttribute('aria-label', 'Fonti documentali');
      
      const header = document.createElement('p');
      header.className = 'text-xs sm:text-sm font-institutional font-semibold text-natan-gray-600 mb-2 sm:mb-3';
      header.textContent = 'Fonti:';
      sourcesSection.appendChild(header);
      
      const sourcesList = document.createElement('div');
      sourcesList.className = 'space-y-1.5 sm:space-y-2';
      message.sources.forEach((source, index) => {
        const link = document.createElement('a');
        link.href = this.escapeHtml(source.url || '#');
        link.target = source.url?.startsWith('#') ? '_self' : '_blank';
        link.rel = source.url?.startsWith('#') ? '' : 'noopener noreferrer';
        link.className = 'flex items-start gap-2 text-xs sm:text-sm text-natan-blue hover:text-natan-blue-dark hover:underline transition-colors';
        link.setAttribute('aria-label', `Fonte ${index + 1}: ${this.escapeHtml(source.title || 'Senza titolo')}`);
        
        const arrow = document.createElement('span');
        arrow.className = 'flex-shrink-0 mt-0.5';
        arrow.textContent = '→';
        link.appendChild(arrow);
        
        const title = document.createElement('span');
        title.className = 'break-words';
        title.textContent = this.escapeHtml(source.title || source.url || 'Senza titolo');
        link.appendChild(title);
        
        sourcesList.appendChild(link);
      });
      sourcesSection.appendChild(sourcesList);
      
      bubble.appendChild(sourcesSection);
    }

    // Metadata (URS, verification status) - mobile-first
    if (message.role === 'assistant' && (message.avgUrs !== undefined && message.avgUrs !== null || message.verificationStatus)) {
      const metadataDiv = document.createElement('div');
      metadataDiv.className = 'mt-3 sm:mt-4 pt-3 border-t border-natan-gray-300 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5 sm:gap-3 text-xs text-natan-gray-500';
      metadataDiv.setAttribute('aria-label', 'Metadati verifica');
      
      if (message.avgUrs !== undefined && message.avgUrs !== null) {
        const ursSpan = document.createElement('span');
        ursSpan.className = 'flex items-center gap-1.5';
        
        const ursLabel = document.createElement('span');
        ursLabel.className = 'font-medium';
        ursLabel.textContent = 'URS Medio:';
        ursSpan.appendChild(ursLabel);
        
        const ursValue = document.createElement('span');
        ursValue.className = typeof message.avgUrs === 'number' && !isNaN(message.avgUrs)
          ? 'font-semibold text-natan-blue-dark'
          : '';
        ursValue.textContent = typeof message.avgUrs === 'number' && !isNaN(message.avgUrs)
          ? message.avgUrs.toFixed(2)
          : 'N/A';
        ursSpan.appendChild(ursValue);
        
        metadataDiv.appendChild(ursSpan);
      }
      
      if (message.verificationStatus) {
        const statusSpan = document.createElement('span');
        statusSpan.className = 'flex items-center gap-1.5 text-natan-gray-600';
        
        const statusLabel = document.createElement('span');
        statusLabel.textContent = 'Status:';
        statusSpan.appendChild(statusLabel);
        
        const statusValue = document.createElement('span');
        statusValue.className = 'font-medium capitalize';
        statusValue.textContent = message.verificationStatus;
        statusSpan.appendChild(statusValue);
        
        metadataDiv.appendChild(statusSpan);
      }
      
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




