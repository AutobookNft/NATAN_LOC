<x-natan.layout :title="$title">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-2xl w-full text-center">
            <div class="mb-6">
                <div class="h-20 w-20 mx-auto rounded-full bg-gradient-to-br from-natan-blue to-natan-blue-dark flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <h1 class="text-3xl font-institutional font-bold text-natan-blue-dark mb-2">
                    {{ $title }}
                </h1>
                <p class="text-lg text-natan-gray-600">
                    {{ $feature }}
                </p>
            </div>
            
            <div class="bg-white rounded-xl border border-natan-gray-300 p-6 shadow-sm">
                <p class="text-natan-gray-700 mb-4">
                    Questa funzionalità è in fase di sviluppo e sarà disponibile a breve.
                </p>
                <a 
                    href="{{ route('natan.chat') }}" 
                    class="btn-primary inline-flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Torna alla Chat
                </a>
            </div>
        </div>
    </div>
</x-natan.layout>














