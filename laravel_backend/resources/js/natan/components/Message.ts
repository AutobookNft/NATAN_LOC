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

    // Action buttons container (only for assistant messages with content)
    if (message.role === 'assistant' && message.content) {
      const actionsContainer = document.createElement('div');
      actionsContainer.className = 'absolute flex items-center gap-1 transition-opacity opacity-0 top-2 right-2 group-hover:opacity-100';

      // Copy button
      const copyButton = this.createActionButton(
        'Copia',
        `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
        </svg>`,
        async (btn) => {
          try {
            await navigator.clipboard.writeText(message.content || '');
            this.showButtonFeedback(btn, 'success');
          } catch (err) {
            console.error('Failed to copy:', err);
            // Fallback
            const textArea = document.createElement('textarea');
            textArea.value = message.content || '';
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            this.showButtonFeedback(btn, 'success');
          }
        }
      );
      actionsContainer.appendChild(copyButton);

      // Export HTML button (for rich content)
      const htmlButton = this.createActionButton(
        'Scarica HTML',
        `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
        </svg>`,
        () => this.exportAsHtml(message)
      );
      actionsContainer.appendChild(htmlButton);

      // Export Excel button (for table content)
      const excelButton = this.createActionButton(
        'Scarica Excel',
        `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>`,
        () => this.exportAsExcel(message)
      );
      actionsContainer.appendChild(excelButton);

      bubble.appendChild(actionsContainer);
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

      // Configure marked for enterprise-grade rendering
      marked.setOptions({
        breaks: true,         // Convert line breaks to <br>
        gfm: true,           // GitHub Flavored Markdown (tables, strikethrough, etc.)
        headerIds: false,    // Disable auto-generated IDs for headers
        mangle: false,       // Don't obfuscate email addresses
      });

      // Separa contenuto principale dalla sezione FONTI per renderla collassabile
      let mainContent = message.content;
      let sourcesContent = '';

      // Pattern per trovare la sezione FONTI (varie forme)
      const fontiPatterns = [
        /\n---\n\n## FONTI CONSULTATE\n([\s\S]*?)$/i,
        /\n## FONTI CONSULTATE\n([\s\S]*?)$/i,
        /\n### FONTI CONSULTATE\n([\s\S]*?)$/i,
        /\n## FONTI\n([\s\S]*?)$/i,
        /\n### FONTI\n([\s\S]*?)$/i,
      ];

      for (const pattern of fontiPatterns) {
        const match = message.content.match(pattern);
        if (match) {
          mainContent = message.content.substring(0, match.index || 0);
          sourcesContent = match[1] || match[0];
          break;
        }
      }

      // Render markdown to HTML (solo contenuto principale)
      const htmlContent = marked.parse(mainContent) as string;

      // Sanitize HTML to prevent XSS attacks
      const sanitizedContent = DOMPurify.sanitize(htmlContent, {
        ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'a', 'blockquote', 'code', 'pre', 'hr', 'div', 'span', 'table', 'thead', 'tbody', 'tr', 'th', 'td'],
        ALLOWED_ATTR: ['href', 'target', 'rel', 'class', 'style', 'id'],
      });

      contentDiv.innerHTML = sanitizedContent;
      bubble.appendChild(contentDiv);

      // Se ci sono fonti, crea sezione collassabile
      if (sourcesContent && message.role === 'assistant') {
        const sourcesSection = document.createElement('div');
        sourcesSection.className = 'pt-3 mt-3 border-t sm:mt-4 sm:pt-4 border-natan-gray-300';

        // Header collassabile
        const headerContainer = document.createElement('div');
        headerContainer.className = 'flex items-center justify-between px-2 py-1 -mx-2 rounded cursor-pointer select-none hover:bg-natan-gray-50';

        // Conta le fonti (linee che iniziano con numero)
        const sourceLines = sourcesContent.trim().split('\n').filter(line => /^\d+\./.test(line.trim()));
        const sourceCount = sourceLines.length || '?';

        const header = document.createElement('p');
        header.className = 'text-xs font-institutional font-semibold text-natan-gray-500 flex items-center gap-1.5';
        header.innerHTML = `
          <svg class="w-3 h-3 transition-transform duration-200 sources-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
          📚 Fonti consultate (${sourceCount})
        `;
        headerContainer.appendChild(header);
        sourcesSection.appendChild(headerContainer);

        // Contenuto fonti (nascosto di default)
        const sourcesListDiv = document.createElement('div');
        sourcesListDiv.className = 'hidden pl-4 mt-2 space-y-1 sources-list';

        // Render fonti come HTML
        const sourcesHtml = marked.parse(sourcesContent) as string;
        const sanitizedSources = DOMPurify.sanitize(sourcesHtml, {
          ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'a', 'ol', 'ul', 'li'],
          ALLOWED_ATTR: ['href', 'target', 'rel', 'class'],
        });
        sourcesListDiv.innerHTML = sanitizedSources;

        // Stile link fonti
        sourcesListDiv.querySelectorAll('a').forEach(link => {
          link.classList.add('text-natan-blue', 'hover:text-natan-blue-dark', 'hover:underline', 'text-xs');
          if (!link.getAttribute('target')) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
          }
        });
        sourcesListDiv.querySelectorAll('li').forEach(li => {
          li.classList.add('text-xs', 'text-natan-gray-600');
        });

        sourcesSection.appendChild(sourcesListDiv);

        // Toggle click
        headerContainer.addEventListener('click', () => {
          const chevron = headerContainer.querySelector('.sources-chevron');
          const list = sourcesSection.querySelector('.sources-list');
          if (list && chevron) {
            const isHidden = list.classList.contains('hidden');
            list.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180', isHidden);
          }
        });

        bubble.appendChild(sourcesSection);
      }
    }

    if (message.role === 'assistant' && message.commandResult && message.commandResult.rows && message.commandResult.rows.length > 0) {
      const commandSection = document.createElement('div');
      commandSection.className = 'pt-3 mt-3 border-t sm:mt-4 sm:pt-4 border-natan-gray-300';
      commandSection.setAttribute('aria-label', `@${message.commandResult.command}`);

      const commandHeader = document.createElement('p');
      commandHeader.className = 'mb-2 text-xs font-semibold sm:text-sm font-institutional text-natan-blue-dark sm:mb-3';
      commandHeader.textContent = `@${message.commandResult.command}`;
      commandSection.appendChild(commandHeader);

      const rowsContainer = document.createElement('div');
      rowsContainer.className = 'space-y-2.5 sm:space-y-3';

      message.commandResult.rows.forEach((row, index) => {
        const rowCard = document.createElement('div');
        rowCard.className = 'px-3 py-3 border rounded-lg shadow-sm border-natan-gray-300 bg-white/80 sm:px-4 sm:py-4';
        rowCard.setAttribute('role', 'group');
        rowCard.setAttribute('aria-label', `@${message.commandResult?.command} #${index + 1}`);

        const title = document.createElement('p');
        title.className = 'mb-1 text-sm font-semibold sm:text-base text-natan-blue-dark';
        title.textContent = this.escapeHtml(row.title ?? '');
        rowCard.appendChild(title);

        if (row.description) {
          const description = document.createElement('p');
          description.className = 'mb-2 text-xs sm:text-sm text-natan-gray-700';
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
          link.className = 'inline-flex items-center gap-2 mt-3 text-xs underline sm:text-sm text-natan-blue hover:text-natan-blue-dark';
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
      claimsSection.className = 'pt-3 mt-4 border-t sm:mt-6 sm:pt-4 border-natan-gray-300';
      claimsSection.setAttribute('aria-label', 'Affermazioni verificate');

      const claimsHeader = document.createElement('p');
      claimsHeader.className = 'mb-2 text-xs font-semibold sm:text-sm font-institutional text-natan-blue-dark sm:mb-3';
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

    // Infographic (se presente - generata per query con "matrice", "grafico", etc.)
    if (message.role === 'assistant' && message.infographic?.chart) {
      const infographicSection = document.createElement('div');
      infographicSection.className = 'pt-4 mt-4 border-t sm:mt-6 border-natan-gray-300';
      infographicSection.setAttribute('aria-label', 'Visualizzazione grafica');

      // Header
      const header = document.createElement('div');
      header.className = 'flex items-center justify-between mb-3';

      const headerTitle = document.createElement('p');
      headerTitle.className = 'flex items-center gap-2 text-xs font-semibold sm:text-sm font-institutional text-natan-blue-dark';
      headerTitle.innerHTML = `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        Visualizzazione: ${message.infographic.chart_type || 'grafico'}
      `;
      header.appendChild(headerTitle);

      // Download button
      const downloadBtn = document.createElement('button');
      downloadBtn.className = 'flex items-center gap-1 text-xs text-natan-blue hover:text-natan-blue-dark';
      downloadBtn.innerHTML = `
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
        Scarica
      `;
      downloadBtn.addEventListener('click', () => this.downloadInfographic(message.infographic!));
      header.appendChild(downloadBtn);

      infographicSection.appendChild(header);

      // Chart container
      const chartContainer = document.createElement('div');
      chartContainer.className = 'p-4 overflow-x-auto bg-white border rounded-lg border-natan-gray-200';

      // Check format: PNG (base64) vs HTML
      const format = message.infographic.format || 'html';

      if (format === 'png' || format === 'svg') {
        // Base64 image - create img tag
        const img = document.createElement('img');
        img.src = `data:image/${format};base64,${message.infographic.chart}`;
        img.alt = message.infographic.title || 'Infografica';
        img.className = 'h-auto max-w-full mx-auto';
        img.style.maxHeight = '500px';
        chartContainer.appendChild(img);
      } else {
        // HTML chart - sanitize and inject
        const sanitizedChart = DOMPurify.sanitize(message.infographic.chart, {
          ALLOWED_TAGS: ['div', 'svg', 'g', 'path', 'rect', 'circle', 'line', 'text', 'tspan', 'defs', 'clipPath', 'style', 'script', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'span', 'p', 'h1', 'h2', 'h3', 'strong', 'em'],
          ALLOWED_ATTR: ['class', 'style', 'id', 'width', 'height', 'viewBox', 'fill', 'stroke', 'stroke-width', 'd', 'transform', 'x', 'y', 'cx', 'cy', 'r', 'rx', 'ry', 'font-size', 'text-anchor', 'dominant-baseline', 'clip-path', 'xmlns'],
          ADD_TAGS: ['plotly-graph'],
          FORCE_BODY: true
        });
        chartContainer.innerHTML = sanitizedChart;
      }

      infographicSection.appendChild(chartContainer);

      // Description (if present)
      if (message.infographic.description) {
        const desc = document.createElement('p');
        desc.className = 'mt-2 text-xs italic text-natan-gray-500';
        desc.textContent = message.infographic.description;
        infographicSection.appendChild(desc);
      }

      bubble.appendChild(infographicSection);
    }

    // Sources (mobile-first with better link styling) - COLLAPSIBLE
    // Mostra solo se ci sono fonti valide e se il contenuto NON contiene già la sezione fonti
    const contentHasSourcesSection = message.content?.includes('FONTI CONSULTATE') || message.content?.includes('Fonti:');
    const validSources = (message.sources || []).filter(s => s.title && s.title.length > 5 && s.title !== 'Senza titolo');

    // DEBUG: Log sources info
    console.log('[Message] Sources check:', {
      role: message.role,
      sourcesCount: message.sources?.length || 0,
      validSourcesCount: validSources.length,
      contentHasSourcesSection,
      willShowCollapsible: message.role === 'assistant' && validSources.length > 0 && !contentHasSourcesSection
    });

    if (message.role === 'assistant' && validSources.length > 0 && !contentHasSourcesSection) {
      const sourcesSection = document.createElement('div');
      sourcesSection.className = 'pt-3 mt-3 border-t sm:mt-4 sm:pt-4 border-natan-gray-300';
      sourcesSection.setAttribute('aria-label', 'Fonti documentali');

      // Collapsible header with toggle
      const headerContainer = document.createElement('div');
      headerContainer.className = 'flex items-center justify-between cursor-pointer select-none';

      const header = document.createElement('p');
      header.className = 'text-xs font-institutional font-semibold text-natan-gray-500 flex items-center gap-1.5';
      header.innerHTML = `
        <svg class="w-3 h-3 transition-transform duration-200 sources-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
        Fonti (${validSources.length})
      `;
      headerContainer.appendChild(header);
      sourcesSection.appendChild(headerContainer);

      const sourcesList = document.createElement('div');
      sourcesList.className = 'hidden mt-2 space-y-1 sources-list';
      validSources.forEach((source, index) => {
        const link = document.createElement('a');

        // Handle internal document references (#doc-{document_id})
        const isInternal = source.url?.startsWith('#doc-');
        let href = this.escapeHtml(source.url || '#');
        let documentId: string = '';

        if (isInternal && source.url) {
          // Convert #doc-{document_id} to /natan/documents/view/{document_id}
          documentId = source.url.replace('#doc-', '');
          href = `/natan/documents/view/${encodeURIComponent(documentId)}`;
        }

        link.href = href;
        link.target = isInternal ? '_self' : '_blank';
        link.rel = isInternal ? '' : 'noopener noreferrer';
        if (isInternal && documentId) {
          link.setAttribute('data-document-id', documentId);
        }
        link.className = 'flex items-start gap-1.5 text-xs text-natan-blue hover:text-natan-blue-dark hover:underline transition-colors py-0.5';
        link.setAttribute('aria-label', `Fonte ${index + 1}: ${this.escapeHtml(source.title || 'Senza titolo')}`);

        const arrow = document.createElement('span');
        arrow.className = 'flex-shrink-0 text-natan-gray-400';
        arrow.textContent = '→';
        link.appendChild(arrow);

        const title = document.createElement('span');
        title.className = 'break-words line-clamp-1';
        title.textContent = this.escapeHtml(source.title || source.url || 'Senza titolo');
        link.appendChild(title);

        sourcesList.appendChild(link);
      });
      sourcesSection.appendChild(sourcesList);

      // Toggle functionality
      headerContainer.addEventListener('click', () => {
        const chevron = headerContainer.querySelector('.sources-chevron');
        const list = sourcesSection.querySelector('.sources-list');
        if (list && chevron) {
          list.classList.toggle('hidden');
          chevron.classList.toggle('rotate-180');
        }
      });

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

  /**
   * Create a styled action button for message actions
   */
  private static createActionButton(
    title: string,
    iconHtml: string,
    onClick: (btn: HTMLButtonElement) => void
  ): HTMLButtonElement {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'p-1.5 rounded-lg bg-natan-gray-100 hover:bg-natan-gray-200 text-natan-gray-600 hover:text-natan-blue-dark transition-colors';
    button.setAttribute('aria-label', title);
    button.setAttribute('title', title);
    button.innerHTML = iconHtml;

    button.addEventListener('click', (e) => {
      e.stopPropagation();
      onClick(button);
    });

    return button;
  }

  /**
   * Show visual feedback on button (success checkmark)
   */
  private static showButtonFeedback(button: HTMLButtonElement, type: 'success' | 'error'): void {
    const originalHTML = button.innerHTML;
    const color = type === 'success' ? 'text-green-600' : 'text-red-600';
    const icon = type === 'success'
      ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>'
      : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';

    button.innerHTML = `<svg class="w-4 h-4 ${color}" fill="none" stroke="currentColor" viewBox="0 0 24 24">${icon}</svg>`;
    button.classList.add(color);

    setTimeout(() => {
      button.innerHTML = originalHTML;
      button.classList.remove(color);
    }, 2000);
  }

  /**
   * Export message content as HTML file
   */
  private static exportAsHtml(message: Message): void {
    const content = message.content || '';

    // Configure marked for rendering
    marked.setOptions({
      breaks: true,
      gfm: true,
      headerIds: false,
      mangle: false,
    });

    const htmlContent = marked.parse(content) as string;
    const sanitizedContent = DOMPurify.sanitize(htmlContent, {
      ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'a', 'blockquote', 'code', 'pre', 'hr', 'div', 'span', 'table', 'thead', 'tbody', 'tr', 'th', 'td'],
      ALLOWED_ATTR: ['href', 'target', 'rel', 'class', 'style'],
    });

    // Create full HTML document with styling
    const fullHtml = `<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NATAN - Report ${new Date().toLocaleDateString('it-IT')}</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      line-height: 1.6;
      max-width: 900px;
      margin: 0 auto;
      padding: 40px 20px;
      color: #1a1a2e;
      background: #f8fafc;
    }
    h1, h2, h3 { color: #1B365D; margin-top: 1.5em; }
    h1 { border-bottom: 3px solid #1B365D; padding-bottom: 0.5em; }
    h2 { border-bottom: 1px solid #e2e8f0; padding-bottom: 0.3em; }
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 1em 0;
      background: white;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    th, td {
      border: 1px solid #e2e8f0;
      padding: 12px;
      text-align: left;
    }
    th {
      background: #1B365D;
      color: white;
      font-weight: 600;
    }
    tr:nth-child(even) { background: #f8fafc; }
    tr:hover { background: #e8f4f8; }
    a { color: #1B365D; text-decoration: underline; }
    a:hover { color: #0d1f3c; }
    strong { color: #1B365D; }
    ul, ol { margin: 1em 0; padding-left: 2em; }
    li { margin-bottom: 0.5em; }
    blockquote {
      border-left: 4px solid #1B365D;
      margin: 1em 0;
      padding: 0.5em 1em;
      background: #f1f5f9;
    }
    code {
      background: #e2e8f0;
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 0.9em;
    }
    pre {
      background: #1a1a2e;
      color: #e2e8f0;
      padding: 1em;
      border-radius: 8px;
      overflow-x: auto;
    }
    .header {
      background: linear-gradient(135deg, #1B365D 0%, #2d4a7c 100%);
      color: white;
      padding: 30px;
      margin: -40px -20px 40px;
      border-radius: 0 0 20px 20px;
    }
    .header h1 { color: white; border: none; margin: 0; }
    .header .meta { opacity: 0.9; margin-top: 10px; font-size: 0.9em; }
    .footer {
      margin-top: 40px;
      padding-top: 20px;
      border-top: 1px solid #e2e8f0;
      text-align: center;
      color: #64748b;
      font-size: 0.9em;
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>📊 Report NATAN</h1>
    <div class="meta">Generato il ${new Date().toLocaleString('it-IT')} • Sistema Intelligence Documentale PA</div>
  </div>

  <main>
    ${sanitizedContent}
  </main>

  <div class="footer">
    <p>Documento generato automaticamente da NATAN - Sistema di Intelligence Documentale per la PA</p>
    <p>© ${new Date().getFullYear()} - I dati contenuti provengono da fonti ufficiali verificate</p>
  </div>
</body>
</html>`;

    // Download
    const blob = new Blob([fullHtml], { type: 'text/html;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `NATAN_Report_${new Date().toISOString().split('T')[0]}.html`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);

    console.log('[Message] HTML exported successfully');
  }

  /**
   * Export message content as Excel (CSV) file
   * Extracts tables from markdown and converts to CSV
   */
  private static exportAsExcel(message: Message): void {
    const content = message.content || '';

    // Extract tables from content (simple markdown table parser)
    const tableRegex = /\|(.+)\|[\r\n]+\|[-:\s|]+\|[\r\n]+((?:\|.+\|[\r\n]*)+)/g;
    const tables: string[][] = [];
    let match;

    while ((match = tableRegex.exec(content)) !== null) {
      const headerRow = match[1].split('|').map(cell => cell.trim()).filter(Boolean);
      const bodyRows = match[2].trim().split('\n').map(row =>
        row.split('|').map(cell => cell.trim()).filter(Boolean)
      );

      tables.push([headerRow, ...bodyRows]);
    }

    if (tables.length === 0) {
      // No tables found, export as plain text CSV
      const lines = content.split('\n').map(line => {
        // Escape quotes and wrap in quotes
        return '"' + line.replace(/"/g, '""').replace(/\*\*/g, '').replace(/\*/g, '') + '"';
      });

      const csv = lines.join('\n');
      this.downloadCsv(csv, 'NATAN_Export');
      return;
    }

    // Convert tables to CSV
    let csv = '';
    tables.forEach((table, index) => {
      if (index > 0) csv += '\n\n'; // Separator between tables

      table.forEach(row => {
        const escapedRow = row.map(cell => {
          // Remove markdown formatting and escape
          let clean = cell
            .replace(/\*\*/g, '')
            .replace(/\*/g, '')
            .replace(/<[^>]+>/g, '') // Remove HTML tags (links, etc.)
            .replace(/\[([^\]]+)\]\([^)]+\)/g, '$1'); // Extract link text

          // Escape quotes and wrap
          if (clean.includes(',') || clean.includes('"') || clean.includes('\n')) {
            clean = '"' + clean.replace(/"/g, '""') + '"';
          }
          return clean;
        });
        csv += escapedRow.join(',') + '\n';
      });
    });

    this.downloadCsv(csv, 'NATAN_Matrix');
  }

  /**
   * Download CSV content as file
   */
  private static downloadCsv(csv: string, filename: string): void {
    // Add BOM for Excel UTF-8 compatibility
    const bom = '\ufeff';
    const blob = new Blob([bom + csv], { type: 'text/csv;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${filename}_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);

    console.log('[Message] CSV/Excel exported successfully');
  }

  /**
   * Download infographic HTML as file
   */
  private static downloadInfographic(infographic: { chart: string; chart_type: string; title?: string }): void {
    const html = infographic.chart;
    const blob = new Blob([html], { type: 'text/html;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    const chartType = infographic.chart_type || 'chart';
    link.download = `NATAN_${chartType}_${new Date().toISOString().split('T')[0]}.html`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);

    console.log('[Message] Infographic downloaded successfully');
  }
}




