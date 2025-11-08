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
    bubble.className = `w-full sm:max-w-3xl rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 shadow-sm transition-shadow relative ${message.role === 'user'
      ? 'bg-natan-blue text-white ml-auto hover:shadow-md'
      : 'bg-white text-natan-gray-900 border border-natan-gray-300 hover:shadow-md'
      }`;

    // Copy button (only for assistant messages)
    if (message.role === 'assistant' && message.content) {
      const copyButton = document.createElement('button');
      copyButton.type = 'button';
      copyButton.className = 'absolute top-2 right-2 p-1.5 rounded-lg bg-natan-gray-100 hover:bg-natan-gray-200 text-natan-gray-600 hover:text-natan-blue-dark transition-colors opacity-0 group-hover:opacity-100';
      copyButton.setAttribute('aria-label', 'Copia risposta');
      copyButton.setAttribute('title', 'Copia risposta');
      
      // Use SVG icon for clipboard
      copyButton.innerHTML = `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
        </svg>
      `;
      
      copyButton.addEventListener('click', async (e) => {
        e.stopPropagation();
        try {
          await navigator.clipboard.writeText(message.content || '');
          // Visual feedback
          const originalHTML = copyButton.innerHTML;
          copyButton.innerHTML = `
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          `;
          copyButton.classList.add('text-green-600');
          setTimeout(() => {
            copyButton.innerHTML = originalHTML;
            copyButton.classList.remove('text-green-600');
          }, 2000);
        } catch (err) {
          console.error('Failed to copy:', err);
          // Fallback: select text
          const textArea = document.createElement('textarea');
          textArea.value = message.content || '';
          document.body.appendChild(textArea);
          textArea.select();
          document.execCommand('copy');
          document.body.removeChild(textArea);
        }
      });
      
      bubble.appendChild(copyButton);
      bubble.classList.add('group'); // Add group class for hover effect
    }

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

    if (message.role === 'assistant' && message.commandResult && message.commandResult.rows && message.commandResult.rows.length > 0) {
      const commandSection = document.createElement('div');
      commandSection.className = 'mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-natan-gray-300';
      commandSection.setAttribute('aria-label', `@${message.commandResult.command}`);

      const commandHeader = document.createElement('p');
      commandHeader.className = 'text-xs sm:text-sm font-institutional font-semibold text-natan-blue-dark mb-2 sm:mb-3';
      commandHeader.textContent = `@${message.commandResult.command}`;
      commandSection.appendChild(commandHeader);

      const rowsContainer = document.createElement('div');
      rowsContainer.className = 'space-y-2.5 sm:space-y-3';

      message.commandResult.rows.forEach((row, index) => {
        const rowCard = document.createElement('div');
        rowCard.className = 'rounded-lg border border-natan-gray-300 bg-white/80 px-3 py-3 sm:px-4 sm:py-4 shadow-sm';
        rowCard.setAttribute('role', 'group');
        rowCard.setAttribute('aria-label', `@${message.commandResult?.command} #${index + 1}`);

        const title = document.createElement('p');
        title.className = 'text-sm sm:text-base font-semibold text-natan-blue-dark mb-1';
        title.textContent = this.escapeHtml(row.title ?? '');
        rowCard.appendChild(title);

        if (row.description) {
          const description = document.createElement('p');
          description.className = 'text-xs sm:text-sm text-natan-gray-700 mb-2';
          description.textContent = this.escapeHtml(row.description);
          rowCard.appendChild(description);
        }

        if (row.metadata && row.metadata.length > 0) {
          const metaList = document.createElement('ul');
          metaList.className = 'space-y-1';
          row.metadata.forEach((meta) => {
            const item = document.createElement('li');
            item.className = 'text-xs sm:text-sm text-natan-gray-600';

            const label = document.createElement('span');
            label.className = 'font-medium text-natan-gray-700';
            label.textContent = `${this.escapeHtml(meta.label)}: `;

            const value = document.createElement('span');
            value.textContent = this.escapeHtml(meta.value);

            item.appendChild(label);
            item.appendChild(value);
            metaList.appendChild(item);
          });
          rowCard.appendChild(metaList);
        }

        if (row.link && row.link.url) {
          const link = document.createElement('a');
          link.href = this.escapeHtml(row.link.url);
          link.className = 'inline-flex items-center gap-2 mt-3 text-xs sm:text-sm text-natan-blue hover:text-natan-blue-dark underline';
          const target = row.link.target ?? '_self';
          link.target = target;
          if (target === '_blank') {
            link.rel = 'noopener noreferrer';
          }

          const arrow = document.createElement('span');
          arrow.className = 'text-base leading-none';
          arrow.textContent = '→';

          const label = document.createElement('span');
          label.textContent = this.escapeHtml(row.link.label ?? row.link.url);

          link.appendChild(arrow);
          link.appendChild(label);
          rowCard.appendChild(link);
        }

        rowsContainer.appendChild(rowCard);
      });

      commandSection.appendChild(rowsContainer);
      bubble.appendChild(commandSection);
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
      claimsContainer.innerHTML = ClaimRenderer.renderClaims(message.claims, message.verificationStatus);
      claimsSection.appendChild(claimsContainer);
      
      bubble.appendChild(claimsSection);
    }

    // CRITICAL: NON mostrare claim bloccati all'utente
    // Anche se bloccati, contengono dati potenzialmente inventati che potrebbero essere interpretati come veri
    // Questo è un rischio legale/pericolo per la PA - meglio non esporre informazioni non verificate
    // I claim bloccati vengono loggati internamente ma NON mostrati all'utente
    // Se ci sono solo claim bloccati, la risposta viene già bloccata dal backend

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
        
        // Handle internal document references (#doc-{document_id})
        const isInternal = source.url?.startsWith('#doc-');
        let href = this.escapeHtml(source.url || '#');
        let documentId: string = '';
        
        if (isInternal && source.url) {
          // Convert #doc-{document_id} to /natan/documents/view/{document_id}
          documentId = source.url.replace('#doc-', '');
          href = `/natan/documents/view/${encodeURIComponent(documentId)}`;
          console.log('[Message] Internal document link:', { original: source.url, documentId, href });
        }
        
        link.href = href;
        link.target = isInternal ? '_self' : '_blank';
        link.rel = isInternal ? '' : 'noopener noreferrer';
        if (isInternal && documentId) {
          link.setAttribute('data-document-id', documentId);
        }
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




