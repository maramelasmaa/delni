# Laravel API Builder

Generates API routes, controllers, and tests in one go.

**Source:** MCP Market – Laravel API Builder

## Features

- **Route Generation**: Create RESTful API routes with proper structure
- **Controller Scaffolding**: Generate API controllers with CRUD operations
- **Resource Classes**: Auto-generate Eloquent API Resource classes
- **Test Generation**: Create corresponding HTTP tests for endpoints
- **Request Validation**: Generate FormRequest classes for validation

## Usage

Use this skill when:

- Building new API endpoints
- Creating CRUD operations for models
- Generating API resources and transformers
- Scaffolding complete API features with tests
- Setting up versioned API routes

## Integration with This Project

This project uses:

- **Laravel 13.13.0** — Modern routing and resource structure
- **Filament v5** — Admin panel for internal management
- **API Resources** — Located in `app/Http/Resources/`
- **API versioning** — Routes organized by version in `routes/api/`
- **Authentication** — Role-based access via Spatie Permissions

**Current project structure:**
- Admin panel routes: `routes/admin.php`
- Public routes: `routes/web.php`
- API routes: `routes/api/v1/` (if implemented)

**Upcoming provider panel:**
- Provider dashboard routes
- Provider API endpoints
- Provider resource classes and validation

When building the provider panel API, use this skill to scaffold endpoints, controllers, and tests quickly.

See also: [laravel-best-practices](../laravel-best-practices) rule §5 (Eloquent Patterns) and §10 (Routing & Controllers).
