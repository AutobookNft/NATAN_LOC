{{-- Form fields reusable for create and edit --}}

{{-- Name --}}
<div>
    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('tenants.name') }} *</label>
    <input type="text" id="name" name="name" value="{{ old('name', $tenant->name ?? '') }}"
        placeholder="{{ __('tenants.name_placeholder') }}"
        required
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
</div>

{{-- Slug --}}
<div>
    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">{{ __('tenants.slug') }} *</label>
    <input type="text" id="slug" name="slug" value="{{ old('slug', $tenant->slug ?? '') }}"
        placeholder="{{ __('tenants.slug_placeholder') }}"
        pattern="[a-z0-9-]+"
        required
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent font-mono">
    <p class="mt-1 text-xs text-gray-500">{{ __('tenants.slug_format') }}</p>
</div>

{{-- Code --}}
<div>
    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">{{ __('tenants.code') }}</label>
    <input type="text" id="code" name="code" value="{{ old('code', $tenant->code ?? '') }}"
        placeholder="{{ __('tenants.code_placeholder') }}"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
</div>

{{-- Entity Type --}}
<div>
    <label for="entity_type" class="block text-sm font-medium text-gray-700 mb-1">{{ __('tenants.entity_type') }} *</label>
    <select id="entity_type" name="entity_type" required
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
        <option value="">{{ __('tenants.entity_type') }}</option>
        <option value="pa" {{ old('entity_type', $tenant->entity_type ?? '') === 'pa' ? 'selected' : '' }}>{{ __('tenants.entity_type_pa') }}</option>
        <option value="company" {{ old('entity_type', $tenant->entity_type ?? '') === 'company' ? 'selected' : '' }}>{{ __('tenants.entity_type_company') }}</option>
        <option value="public_entity" {{ old('entity_type', $tenant->entity_type ?? '') === 'public_entity' ? 'selected' : '' }}>{{ __('tenants.entity_type_public_entity') }}</option>
        <option value="other" {{ old('entity_type', $tenant->entity_type ?? '') === 'other' ? 'selected' : '' }}>{{ __('tenants.entity_type_other') }}</option>
    </select>
</div>

{{-- Contact Info --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('tenants.email') }}</label>
        <input type="email" id="email" name="email" value="{{ old('email', $tenant->email ?? '') }}"
            placeholder="{{ __('tenants.email_placeholder') }}"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
    </div>
    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('tenants.phone') }}</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $tenant->phone ?? '') }}"
            placeholder="{{ __('tenants.phone_placeholder') }}"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
    </div>
</div>

{{-- Address --}}
<div>
    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">{{ __('tenants.address') }}</label>
    <textarea id="address" name="address" rows="3"
        placeholder="{{ __('tenants.address_placeholder') }}"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">{{ old('address', $tenant->address ?? '') }}</textarea>
</div>

{{-- VAT Number --}}
<div>
    <label for="vat_number" class="block text-sm font-medium text-gray-700 mb-1">{{ __('tenants.vat_number') }}</label>
    <input type="text" id="vat_number" name="vat_number" value="{{ old('vat_number', $tenant->vat_number ?? '') }}"
        placeholder="{{ __('tenants.vat_number_placeholder') }}"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
</div>

{{-- Status and Dates --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="flex items-center">
        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $tenant->is_active ?? true) ? 'checked' : '' }}
            class="w-4 h-4 text-[#1B365D] border-gray-300 rounded focus:ring-[#1B365D]">
        <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">{{ __('tenants.is_active') }}</label>
    </div>
    <div>
        <label for="trial_ends_at" class="block text-sm font-medium text-gray-700 mb-1">{{ __('tenants.trial_ends_at') }}</label>
        <input type="date" id="trial_ends_at" name="trial_ends_at" value="{{ old('trial_ends_at', $tenant->trial_ends_at?->format('Y-m-d') ?? '') }}"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
    </div>
    <div>
        <label for="subscription_ends_at" class="block text-sm font-medium text-gray-700 mb-1">{{ __('tenants.subscription_ends_at') }}</label>
        <input type="date" id="subscription_ends_at" name="subscription_ends_at" value="{{ old('subscription_ends_at', $tenant->subscription_ends_at?->format('Y-m-d') ?? '') }}"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
    </div>
</div>

{{-- Notes --}}
<div>
    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">{{ __('tenants.notes') }}</label>
    <textarea id="notes" name="notes" rows="4"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">{{ old('notes', $tenant->notes ?? '') }}</textarea>
</div>


