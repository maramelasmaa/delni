# Laravel Debugging Assistant

Reads Laravel exception stack traces and suggests fixes.

**Source:** MCP Market (search "Laravel Debugging")

## Features

- **Stack Trace Analysis**: Parse and understand Laravel error logs
- **Root Cause Detection**: Identify the source of common Laravel errors
- **Fix Suggestions**: Provide actionable solutions for exceptions
- **Log Integration**: Read from Laravel logs in `storage/logs/`
- **Context Understanding**: Understand database state, config, and middleware context

## Usage

Use this skill when:

- An exception occurs and you need to understand the stack trace
- Debugging production errors
- Analyzing failed test results
- Tracing error origins through middleware
- Understanding database or Eloquent errors

## Integration with This Project

This project uses:

- **Laravel 13.13.0** with Laravel Boost MCP
- **Error logs** in `storage/logs/laravel.log`
- **Browser logs** accessible via Laravel Boost's `browser-logs` tool
- **Exception handling** in `app/Exceptions/Handler.php`

**Available debugging tools:**
- `php artisan tail` — Stream logs in real-time
- Laravel Boost's `browser-logs` tool — See client-side errors
- `php artisan tinker` — Interactive PHP debugging
- Tests with detailed failure messages

When encountering errors, use this skill along with Laravel Boost's `last-error` tool for comprehensive debugging.

See also: [laravel-best-practices](../laravel-best-practices) rule §13 (Error Handling).
