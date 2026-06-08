# Laravel Testing & Quality Skill

Automates PHPUnit/Pest test generation, HTTP tests, and database refresh setup.

**Source:** MCP Market – Laravel Testing & Quality

## Features

- **Test Generation**: Auto-generate PHPUnit test files with proper structure
- **HTTP Tests**: Create feature tests for routes and controllers
- **Database Setup**: Automated `RefreshDatabase` and factory setup in tests
- **Test Helpers**: Quick assertions and mocking for common patterns
- **Quality Checks**: Integrate with Laravel Pint for code formatting validation

## Usage

Use this skill when:

- Writing tests for new features
- Creating HTTP endpoint tests
- Setting up database-backed feature tests
- Generating test boilerplate
- Ensuring test code follows project conventions

## Integration with This Project

This project uses **PHPUnit v12** with the following patterns:

- Feature tests in `tests/Feature/`
- Unit tests in `tests/Unit/`
- Database tests use `RefreshDatabase` trait
- Factories for model creation
- Pint for code formatting (`vendor/bin/pint --dirty --format agent`)

**Current test suite:** 368 tests, all passing ✅

When creating new tests for provider panel features, reference this skill for test generation shortcuts.

See also: [laravel-best-practices](../laravel-best-practices) rule §8 (Testing Patterns).
