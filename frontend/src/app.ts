/**
 * Main App Component
 * Initializes and manages the NATAN chat interface
 */

import { ChatInterface } from './components/ChatInterface';

export class App {
  private container: HTMLElement;
  private chatInterface: ChatInterface;

  constructor(container: HTMLElement) {
    this.container = container;
    this.render();
    this.chatInterface = new ChatInterface(this.container);
  }

  private render(): void {
    this.container.innerHTML = `
      <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm border-b border-gray-200">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <h1 class="text-2xl font-bold text-gray-900">
              NATAN - Assistente AI PA
            </h1>
            <p class="text-sm text-gray-600 mt-1">
              Ultra Semantic Engine per la Pubblica Amministrazione
            </p>
          </div>
        </header>
        <main id="chat-container" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <!-- Chat interface will be rendered here -->
        </main>
      </div>
    `;
  }
}

