@props([
    'selected' => 'auto',
])

<div class="flex items-center gap-2 overflow-x-auto pb-2">
    <label class="flex items-center gap-2 text-xs sm:text-sm text-natan-gray-700 whitespace-nowrap">
        <span class="font-medium">{{ __('natan.persona.select_title') }}:</span>
    </label>
    
    <select
        id="persona-selector"
        name="persona"
        class="flex-1 min-w-0 rounded-lg border border-natan-gray-300 bg-white px-3 py-1.5 text-xs sm:text-sm focus:border-natan-blue focus:outline-none focus:ring-2 focus:ring-natan-blue/20"
        aria-label="{{ __('natan.persona.select_title') }}"
    >
        <option value="auto" {{ $selected === 'auto' ? 'selected' : '' }}>
            {{ __('natan.persona.auto_mode') }}
        </option>
        <option value="strategic" {{ $selected === 'strategic' ? 'selected' : '' }}>
            {{ __('natan.persona.strategic') }}
        </option>
        <option value="technical" {{ $selected === 'technical' ? 'selected' : '' }}>
            {{ __('natan.persona.technical') }}
        </option>
        <option value="legal" {{ $selected === 'legal' ? 'selected' : '' }}>
            {{ __('natan.persona.legal') }}
        </option>
        <option value="financial" {{ $selected === 'financial' ? 'selected' : '' }}>
            {{ __('natan.persona.financial') }}
        </option>
        <option value="urban_social" {{ $selected === 'urban_social' ? 'selected' : '' }}>
            {{ __('natan.persona.urban_social') }}
        </option>
        <option value="communication" {{ $selected === 'communication' ? 'selected' : '' }}>
            {{ __('natan.persona.communication') }}
        </option>
        <option value="archivist" {{ $selected === 'archivist' ? 'selected' : '' }}>
            {{ __('natan.persona.archivist') }}
        </option>
    </select>
</div>

