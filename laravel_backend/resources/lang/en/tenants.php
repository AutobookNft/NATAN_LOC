<?php

/**
 * @package Resources\Lang\En
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Translations for tenant management (PA & Enterprises)
 */

return [
    // Success messages
    'created_successfully' => 'Tenant created successfully',
    'updated_successfully' => 'Tenant updated successfully',
    'deleted_successfully' => 'Tenant deleted successfully',
    
    // Form labels
    'name' => 'Name',
    'slug' => 'Slug',
    'code' => 'Code',
    'entity_type' => 'Entity Type',
    'email' => 'Email',
    'phone' => 'Phone',
    'address' => 'Address',
    'vat_number' => 'VAT Number / Tax Code',
    'is_active' => 'Active',
    'trial_ends_at' => 'Trial Ends At',
    'subscription_ends_at' => 'Subscription Ends At',
    'notes' => 'Notes',
    
    // Entity types
    'entity_type_pa' => 'Public Administration',
    'entity_type_company' => 'Private Company',
    'entity_type_public_entity' => 'Public Entity',
    'entity_type_other' => 'Other',
    
    // Placeholders
    'name_placeholder' => 'E.g: City of Florence',
    'slug_placeholder' => 'E.g: firenze',
    'code_placeholder' => 'Optional identification code',
    'email_placeholder' => 'email@example.com',
    'phone_placeholder' => '+39 055 1234567',
    'address_placeholder' => 'Full address',
    'vat_number_placeholder' => 'VAT Number or Tax Code',
    
    // Actions
    'create' => 'Create Tenant',
    'edit' => 'Edit Tenant',
    'delete' => 'Delete Tenant',
    'view' => 'View Tenant',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'back' => 'Back',
    'search' => 'Search tenants...',
    'filter' => 'Filter',
    'reset' => 'Reset Filters',
    'sort_by_name' => 'Name',
    'sort_by_date' => 'Creation Date',
    'sort_by_type' => 'Entity Type',
    'sort_asc' => 'Ascending',
    'sort_desc' => 'Descending',
    
    // Stats labels
    'total' => 'Total',
    'active' => 'Active',
    'inactive' => 'Inactive',
    'pa_entities' => 'PA Entities',
    'companies' => 'Companies',
    
    // Detail labels
    'details' => 'Tenant Details',
    'statistics' => 'Statistics',
    'users_count' => 'Users',
    'documents_count' => 'Documents',
    'conversations_count' => 'Conversations',
    'messages_count' => 'Messages',
    'memories_count' => 'Memories',
    
    // Validation messages
    'slug_required' => 'Slug is required',
    'slug_unique' => 'This slug is already in use',
    'slug_format' => 'Slug can only contain lowercase letters, numbers and hyphens',
    'name_required' => 'Name is required',
    'entity_type_required' => 'Entity type is required',
];
