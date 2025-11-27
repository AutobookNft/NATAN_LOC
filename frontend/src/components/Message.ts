/**
 * Message Component
 * Renders a single chat message (user or assistant)
 * Mobile-first with optimized markdown rendering
 */

import type { Message } from '../types';
import { ClaimRenderer } from './ClaimRenderer';
import { UrsBadge } from './UrsBadge';
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
      
      // Configure marked for enterprise-grade rendering
      marked.setOptions({
        breaks: true,         // Convert line breaks to <br>
        gfm: true,           // GitHub Flavored Markdown (tables, strikethrough, etc.)
        headerIds: false,    // Disable auto-generated IDs for headers
        mangle: false,       // Don't obfuscate email addresses
      });

      // Render markdown to HTML
      const htmlContent = marked.parse(message.content) as string;
      
      // Sanitize HTML to prevent XSS attacks
      const sanitizedContent = DOMPurify.sanitize(htmlContent, {
        ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'a', 'blockquote', 'code', 'pre', 'hr', 'div', 'span', 'table', 'thead', 'tbody', 'tr', 'th', 'td'],
        ALLOWED_ATTR: ['href', 'target', 'rel', 'class', 'style', 'id'],
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

    // RAG-Fortress URS Badge (priority over legacy avgUrs)
    if (message.role === 'assistant' && (message.urs_score !== undefined && message.urs_score !== null)) {
      const ursSection = document.createElement('div');
      ursSection.className = 'mt-3 sm:mt-4 pt-3 border-t border-natan-gray-300';
      ursSection.setAttribute('aria-label', 'Ultra Reliability Score');
      
      const ursBadge = UrsBadge.render(message.urs_score, message.urs_explanation);
      ursSection.appendChild(ursBadge);
      
      bubble.appendChild(ursSection);
    } else if (message.role === 'assistant' && (message.avgUrs !== undefined && message.avgUrs !== null)) {
      // Legacy URS display (fallback)
      const metadataDiv = document.createElement('div');
      metadataDiv.className = 'mt-3 sm:mt-4 pt-3 border-t border-natan-gray-300 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5 sm:gap-3 text-xs text-natan-gray-500';
      metadataDiv.setAttribute('aria-label', 'Metadati verifica');
      
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

    // RAG-Fortress Claims Used (claims_used)
    if (message.role === 'assistant' && message.claims_used?.length) {
      const claimsUsedSection = document.createElement('div');
      claimsUsedSection.className = 'mt-3 sm:mt-4 pt-3 border-t border-natan-gray-300';
      claimsUsedSection.setAttribute('aria-label', 'Claim utilizzate');
      
      const header = document.createElement('p');
      header.className = 'text-xs sm:text-sm font-institutional font-semibold text-natan-blue-dark mb-2 sm:mb-3';
      header.textContent = 'Claim verificate utilizzate:';
      claimsUsedSection.appendChild(header);
      
      const claimsList = document.createElement('div');
      claimsList.className = 'space-y-1.5 sm:space-y-2';
      message.claims_used.forEach((claim, index) => {
        const claimItem = document.createElement('div');
        claimItem.className = 'flex items-start gap-2 text-xs sm:text-sm text-natan-gray-700';
        
        const claimNumber = document.createElement('span');
        claimNumber.className = 'font-mono font-bold text-natan-blue';
        claimNumber.textContent = claim; // Es. "(CLAIM_001)"
        claimItem.appendChild(claimNumber);
        
        claimsList.appendChild(claimItem);
      });
      claimsUsedSection.appendChild(claimsList);
      
      bubble.appendChild(claimsUsedSection);
    }

    // RAG-Fortress Sources (sources_list)
    if (message.role === 'assistant' && message.sources_list?.length) {
      const sourcesSection = document.createElement('div');
      sourcesSection.className = 'mt-3 sm:mt-4 pt-3 border-t border-natan-gray-300';
      sourcesSection.setAttribute('aria-label', 'Fonti documentali');
      
      const header = document.createElement('p');
      header.className = 'text-xs sm:text-sm font-institutional font-semibold text-natan-gray-600 mb-2 sm:mb-3';
      header.textContent = 'Fonti utilizzate:';
      sourcesSection.appendChild(header);
      
      const sourcesList = document.createElement('div');
      sourcesList.className = 'space-y-1.5 sm:space-y-2';
      message.sources_list.forEach((source, index) => {
        const sourceItem = document.createElement('div');
        sourceItem.className = 'flex items-start gap-2 text-xs sm:text-sm text-natan-blue hover:text-natan-blue-dark transition-colors';
        
        const arrow = document.createElement('span');
        arrow.className = 'flex-shrink-0 mt-0.5';
        arrow.textContent = '→';
        sourceItem.appendChild(arrow);
        
        const sourceText = document.createElement('span');
        sourceText.className = 'break-words';
        sourceText.textContent = this.escapeHtml(source);
        sourceItem.appendChild(sourceText);
        
        sourcesList.appendChild(sourceItem);
      });
      sourcesSection.appendChild(sourcesList);
      
      bubble.appendChild(sourcesSection);
    }

    // RAG-Fortress Gaps Detected
    if (message.role === 'assistant' && message.gaps_detected?.length) {
      const gapsSection = document.createElement('div');
      gapsSection.className = 'mt-3 sm:mt-4 p-3 sm:p-4 bg-yellow-50 border border-yellow-200 rounded-lg';
      gapsSection.setAttribute('role', 'alert');
      gapsSection.setAttribute('aria-label', 'Gap di copertura rilevati');
      
      const header = document.createElement('p');
      header.className = 'font-institutional font-semibold text-yellow-800 text-sm sm:text-base mb-2';
      header.textContent = '⚠️ Informazioni non completamente coperte';
      gapsSection.appendChild(header);
      
      const description = document.createElement('p');
      description.className = 'text-xs sm:text-sm text-yellow-700 mb-2 leading-relaxed';
      description.textContent = 'Alcune parti della domanda non sono completamente coperte dalle informazioni verificate disponibili.';
      gapsSection.appendChild(description);
      
      const gapsList = document.createElement('ul');
      gapsList.className = 'mt-2 space-y-1.5 text-xs sm:text-sm text-yellow-700 list-disc list-inside';
      message.gaps_detected.forEach(gap => {
        const li = document.createElement('li');
        li.textContent = this.escapeHtml(gap.replace(/^GAP_\d+:\s*/, '')); // Rimuovi prefisso GAP_XX:
        gapsList.appendChild(li);
      });
      gapsSection.appendChild(gapsList);
      
      bubble.appendChild(gapsSection);
    }

    // RAG-Fortress Hallucinations Found
    if (message.role === 'assistant' && message.hallucinations_found?.length) {
      const hallucinationsSection = document.createElement('div');
      hallucinationsSection.className = 'mt-3 sm:mt-4 p-3 sm:p-4 bg-red-50 border border-red-200 rounded-lg';
      hallucinationsSection.setAttribute('role', 'alert');
      hallucinationsSection.setAttribute('aria-label', 'Allucinazioni rilevate');
      
      const header = document.createElement('p');
      header.className = 'font-institutional font-semibold text-red-800 text-sm sm:text-base mb-2';
      header.textContent = '🚨 Allucinazioni rilevate';
      hallucinationsSection.appendChild(header);
      
      const description = document.createElement('p');
      description.className = 'text-xs sm:text-sm text-red-700 mb-2 leading-relaxed';
      description.textContent = 'Il fact-checker ha rilevato informazioni nella risposta che non sono supportate dalle evidenze verificate.';
      hallucinationsSection.appendChild(description);
      
      const hallucinationsList = document.createElement('ul');
      hallucinationsList.className = 'mt-2 space-y-1.5 text-xs sm:text-sm text-red-700 list-disc list-inside';
      message.hallucinations_found.forEach(hallucination => {
        const li = document.createElement('li');
        li.textContent = this.escapeHtml(hallucination.replace(/^HALLUCINATION:\s*/, '')); // Rimuovi prefisso HALLUCINATION:
        hallucinationsList.appendChild(li);
      });
      hallucinationsSection.appendChild(hallucinationsList);
      
      bubble.appendChild(hallucinationsSection);
    }

    // Export Actions (Excel + HTML) - Only for assistant messages with content
    if (message.role === 'assistant' && message.content && this.isExportableContent(message.content)) {
      const exportSection = document.createElement('div');
      exportSection.className = 'mt-3 sm:mt-4 pt-3 border-t border-natan-gray-300 flex flex-wrap items-center gap-2 sm:gap-3';
      exportSection.setAttribute('aria-label', 'Azioni di esportazione');
      
      const exportLabel = document.createElement('span');
      exportLabel.className = 'text-xs sm:text-sm font-institutional font-medium text-natan-gray-600';
      exportLabel.textContent = 'Esporta:';
      exportSection.appendChild(exportLabel);
      
      // Excel Export Button
      const excelButton = document.createElement('button');
      excelButton.type = 'button';
      excelButton.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1';
      excelButton.setAttribute('aria-label', 'Scarica come Excel');
      excelButton.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
        <span>Excel</span>
      `;
      excelButton.addEventListener('click', () => this.exportToExcel(message));
      exportSection.appendChild(excelButton);
      
      // HTML Export Button
      const htmlButton = document.createElement('button');
      htmlButton.type = 'button';
      htmlButton.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-medium text-white bg-natan-blue hover:bg-natan-blue-dark rounded-lg transition-colors shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-natan-blue focus:ring-offset-1';
      htmlButton.setAttribute('aria-label', 'Scarica come HTML');
      htmlButton.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
        </svg>
        <span>HTML</span>
      `;
      htmlButton.addEventListener('click', () => this.exportToHTML(message));
      exportSection.appendChild(htmlButton);
      
      bubble.appendChild(exportSection);
    }

    messageDiv.appendChild(bubble);
    return messageDiv;
  }

  /**
   * Check if content is exportable (contains tables or structured data)
   */
  private static isExportableContent(content: string): boolean {
    // Check for markdown tables (|...|...|)
    const hasTable = /\|.*\|.*\|/gm.test(content);
    // Check for lists or structured data
    const hasLists = /^[-*+]\s+/gm.test(content) || /^\d+\.\s+/gm.test(content);
    // Check for headings (indicates structured document)
    const hasHeadings = /^#{1,6}\s+/gm.test(content);
    
    return hasTable || (hasLists && hasHeadings);
  }

  /**
   * Export message content to Excel file
   */
  private static async exportToExcel(message: Message): Promise<void> {
    try {
      const content = message.content;
      if (!content) return;

      // Parse markdown tables and structured data
      const tables = this.parseMarkdownTables(content);
      
      if (tables.length === 0) {
        alert('Nessuna tabella trovata nel contenuto da esportare.');
        return;
      }

      // Create CSV content (Excel-compatible)
      let csvContent = '';
      
      tables.forEach((table, tableIndex) => {
        if (tableIndex > 0) {
          csvContent += '\n\n'; // Separate multiple tables
        }
        
        // Add table header
        csvContent += table.headers.map(h => `"${h.replace(/"/g, '""')}"`).join(',') + '\n';
        
        // Add table rows
        table.rows.forEach(row => {
          csvContent += row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',') + '\n';
        });
      });

      // Create blob and download
      const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' }); // BOM for Excel
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `natan-export-${new Date().toISOString().split('T')[0]}.csv`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
      
      console.log('✅ Excel export completed');
    } catch (error) {
      console.error('❌ Error exporting to Excel:', error);
      alert('Errore durante l\'esportazione in Excel. Verifica la console per dettagli.');
    }
  }

  /**
   * Export message content to HTML file
   */
  private static async exportToHTML(message: Message): Promise<void> {
    try {
      const content = message.content;
      if (!content) return;

      // Configure marked for HTML export
      marked.setOptions({
        breaks: true,
        gfm: true,
        headerIds: false,
        mangle: false,
      });

      // Render markdown to HTML
      const bodyContent = marked.parse(content) as string;
      
      // Sanitize HTML
      const sanitizedContent = DOMPurify.sanitize(bodyContent, {
        ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'a', 'blockquote', 'code', 'pre', 'hr', 'div', 'span', 'table', 'thead', 'tbody', 'tr', 'th', 'td'],
        ALLOWED_ATTR: ['href', 'target', 'rel', 'class', 'style'],
      });

      // Create complete HTML document
      const htmlDocument = `<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NATAN - Report Esportato</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background: #f9fafb;
            padding: 2rem;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            border-bottom: 3px solid #1B365D;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }
        .header h1 {
            color: #1B365D;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .header .meta {
            color: #6b7280;
            font-size: 0.875rem;
        }
        .content {
            color: #374151;
        }
        .content h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1B365D;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .content h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1B365D;
            margin-top: 1.75rem;
            margin-bottom: 0.875rem;
        }
        .content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #374151;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .content p {
            margin-bottom: 1rem;
            line-height: 1.75;
        }
        .content ul, .content ol {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
        .content li {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }
        .content a {
            color: #1B365D;
            text-decoration: underline;
        }
        .content a:hover {
            color: #0f1d36;
        }
        .content strong {
            font-weight: 600;
            color: #1f2937;
        }
        .content code {
            background: #f3f4f6;
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            font-family: "JetBrains Mono", monospace;
            font-size: 0.875em;
            color: #1B365D;
        }
        .content pre {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 1rem;
            overflow-x: auto;
            margin-bottom: 1rem;
        }
        .content pre code {
            background: none;
            padding: 0;
        }
        .content blockquote {
            border-left: 4px solid #1B365D;
            padding-left: 1rem;
            color: #6b7280;
            font-style: italic;
            margin-bottom: 1rem;
        }
        .content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        .content th {
            background: #1B365D;
            color: white;
            font-weight: 600;
            padding: 0.75rem;
            text-align: left;
            border: 1px solid #0f1d36;
        }
        .content td {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
        }
        .content tr:nth-child(even) {
            background: #f9fafb;
        }
        .footer {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤖 NATAN - Cognitive Trust Layer</h1>
            <div class="meta">
                Report generato il ${new Date().toLocaleDateString('it-IT', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })}
            </div>
        </div>
        <div class="content">
            ${sanitizedContent}
        </div>
        <div class="footer">
            <p>Documento generato da NATAN - Non immagina. Dimostra.</p>
        </div>
    </div>
</body>
</html>`;

      // Create blob and download
      const blob = new Blob([htmlDocument], { type: 'text/html;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `natan-report-${new Date().toISOString().split('T')[0]}.html`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
      
      console.log('✅ HTML export completed');
    } catch (error) {
      console.error('❌ Error exporting to HTML:', error);
      alert('Errore durante l\'esportazione in HTML. Verifica la console per dettagli.');
    }
  }

  /**
   * Parse markdown tables from content
   */
  private static parseMarkdownTables(content: string): Array<{ headers: string[], rows: string[][] }> {
    const tables: Array<{ headers: string[], rows: string[][] }> = [];
    const lines = content.split('\n');
    
    let inTable = false;
    let currentTable: { headers: string[], rows: string[][] } | null = null;
    
    for (let i = 0; i < lines.length; i++) {
      const line = lines[i].trim();
      
      // Check if line is a table row
      if (line.startsWith('|') && line.endsWith('|')) {
        const cells = line
          .split('|')
          .slice(1, -1) // Remove first and last empty cells
          .map(cell => cell.trim());
        
        // Check if next line is separator (|----|----| or |:---|---:|)
        const nextLine = i + 1 < lines.length ? lines[i + 1].trim() : '';
        const isSeparator = /^\|[\s:-]+\|/.test(nextLine);
        
        if (!inTable && isSeparator) {
          // Start new table with headers
          currentTable = { headers: cells, rows: [] };
          inTable = true;
          i++; // Skip separator line
        } else if (inTable && currentTable && !isSeparator) {
          // Add row to current table
          currentTable.rows.push(cells);
        }
      } else if (inTable && currentTable) {
        // End of table
        tables.push(currentTable);
        currentTable = null;
        inTable = false;
      }
    }
    
    // Add last table if still open
    if (inTable && currentTable) {
      tables.push(currentTable);
    }
    
    return tables;
  }

  private static escapeHtml(text: string): string {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}




