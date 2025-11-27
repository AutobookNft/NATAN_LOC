@extends('layouts.natan-pro')

@section('title', 'Progetto NATAN')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Component container with data attribute --}}
    <div id="project-detail-container" data-project-id="{{ $projectId }}"></div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/projects.ts'])
@endpush

