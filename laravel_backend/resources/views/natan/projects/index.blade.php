@extends('layouts.natan-pro')

@section('title', 'Progetti NATAN')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Component container --}}
    <div id="projects-list-container"></div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/projects.ts'])
    <script>
        // Initialize component when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            console.log('[NATAN][Projects] Initializing ProjectList component');
            
            try {
                new window.ProjectList('projects-list-container');
                console.log('[NATAN][Projects] ProjectList initialized successfully');
            } catch (error) {
                console.error('[NATAN][Projects] Failed to initialize ProjectList:', error);
            }
        });
    </script>
@endpush

