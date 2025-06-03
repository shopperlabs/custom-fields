# Tenant Context Documentation

## Overview

The Custom Fields package now supports Context-aware tenant scoping that works seamlessly across both web requests and queue jobs. This enhancement ensures that tenant isolation is maintained even when operations are performed asynchronously.

## How It Works

The tenant context system uses Laravel's Context feature to store and retrieve tenant information across different execution contexts. This allows the system to:

- Automatically scope queries to the current tenant in web requests
- Maintain tenant context in queued jobs
- Provide manual tenant context management for complex scenarios

## Configuration

Enable tenant awareness in your `config/custom-fields.php`:

```php
'tenant_aware' => true,
'column_names' => [
    'tenant_foreign_key' => 'tenant_id',
],
```

## Automatic Features

### Web Requests

The `SetTenantContextMiddleware` automatically sets the tenant context from Filament's current tenant for all web requests. This middleware is automatically registered when tenant awareness is enabled.

### Database Scoping

The `TenantScope` automatically filters all custom field queries to the current tenant context. It works by:

1. First checking Laravel Context for tenant ID (works in queues)
2. Falling back to Filament's current tenant (works in web requests)
3. Applying the appropriate WHERE clause to scope the query

## Manual Usage

### TenantContextService

The `TenantContextService` provides methods for manual tenant context management:

```php
use Relaticle\CustomFields\Services\TenantContextService;

// Set tenant context manually
TenantContextService::setTenantId(123);

// Get current tenant ID
$tenantId = TenantContextService::getCurrentTenantId();

// Set from Filament tenant
TenantContextService::setFromFilamentTenant();

// Execute callback with specific tenant context
TenantContextService::withTenant(123, function () {
    // Code here runs with tenant 123 context
});

// Clear tenant context
TenantContextService::clearTenantContext();

// Check if tenant context is available
if (TenantContextService::hasTenantContext()) {
    // Tenant context is set
}
```

### Queue Jobs

For queue jobs that need tenant context, use the `TenantAware` trait:

```php
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Relaticle\CustomFields\Jobs\Concerns\TenantAware;

class ProcessCustomFieldData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAware;

    public function handle(): void
    {
        // This code automatically runs with the correct tenant context
        // Custom field queries will be scoped to the tenant that dispatched this job
    }
}
```

When dispatching jobs, the trait automatically captures the current tenant context:

```php
// The job will automatically inherit the current tenant context
ProcessCustomFieldData::dispatch();

// Or explicitly set a tenant
ProcessCustomFieldData::dispatch()->withTenant(123);
```

### Alternative Job Handling

If you prefer not to use the trait, you can manually handle tenant context:

```php
public function handle(): void
{
    TenantContextService::withTenant($this->tenantId, function () {
        // Your job logic here
    });
}
```

## Middleware Registration

The tenant context middleware is automatically registered and applied to Filament panels when tenant awareness is enabled. If you need to apply it to other routes, you can use the `tenant-context` middleware alias:

```php
Route::middleware(['tenant-context'])->group(function () {
    // Routes that need tenant context
});
```

## Best Practices

### 1. Always Use the Service

When you need to work with tenant context programmatically, always use `TenantContextService` rather than directly accessing Context or Filament facades.

### 2. Queue Job Pattern

For queue jobs that work with custom fields:

```php
class SomeJob implements ShouldQueue
{
    use TenantAware;

    public function handle(): void
    {
        // Custom field operations here will be automatically scoped
        $customFields = CustomField::all(); // Only returns current tenant's fields
    }
}
```

### 3. Testing

When writing tests, you can manually set tenant context:

```php
public function test_custom_field_scoping(): void
{
    TenantContextService::setTenantId(1);
    
    // Your test code here
    
    TenantContextService::clearTenantContext();
}
```

### 4. Background Processing

For long-running background processes, consider refreshing tenant context periodically:

```php
public function processInBackground(): void
{
    TenantContextService::setFromFilamentTenant();
    
    // Long-running operations
}
```

## Migration Notes

If you're upgrading from a previous version:

1. The tenant scope now works automatically in queue jobs
2. No changes are required to existing code
3. The system is backward compatible with existing implementations
4. Queue jobs will now properly respect tenant boundaries

## Troubleshooting

### Queue Jobs Not Respecting Tenant Scope

Ensure your jobs use the `TenantAware` trait or manually handle tenant context in the `handle` method.

### Context Not Available in Tests

Manually set the tenant context in your test setup:

```php
protected function setUp(): void
{
    parent::setUp();
    TenantContextService::setTenantId(1);
}
```

### Middleware Not Applied

The middleware is automatically applied to Filament panels. For custom routes, ensure you're using the `tenant-context` middleware.