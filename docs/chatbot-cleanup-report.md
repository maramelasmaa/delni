# Chatbot Cleanup Report

Date: 2026-06-11

## Summary

The old chatbot implementation was removed from runtime code, UI, routes, service container wiring, tests, and stale root documentation. DeepSeek configuration and reusable AI infrastructure were preserved for future rebuild work.

## Deleted Chatbot Runtime

- API controllers: `ChatController`, `ChatControllerV2`, `ChatControllerV3`
- Chat request layer: `SendMessageRequest`
- Chatbot DTOs: `ExtractedIntent`, `ProviderChatResultDTO`
- Chatbot service provider and middleware
- Chatbot service namespace: `app/Services/Chatbot`
- Chatbot widget Blade component and public layout include
- Chatbot-only seeder and `config/delni_service_aliases.php`
- Chatbot-only feature tests
- Stale root chatbot Markdown docs; this report is the retained source of truth

## Preserved DeepSeek Infrastructure

- `config/deepseek.php`
- `.env` DeepSeek keys and settings
- `app/Services/AI/DeepSeekClient.php`
- `app/Services/AI/ApiUsageTracker.php`
- `app/Models/ApiUsageLog.php`
- `database/migrations/2026_06_11_create_api_usage_logs_table.php`

The preserved `DeepSeekClient` is generic AI HTTP infrastructure only: it sends chat-completion messages, handles timeouts/retries/logging, and extracts safe response content. It contains no marketplace, provider-search, chatbot, conversation, or intent logic.

## Routes Removed

Removed all `/api/chat/*` endpoints:

- `GET /api/chat/init`
- `POST /api/chat/message`
- `POST /api/chat/reset`
- `GET /api/chat/v2/init`
- `POST /api/chat/v2/message`
- `POST /api/chat/v2/reset`
- `GET /api/chat/v3/init`
- `POST /api/chat/v3/message`
- `POST /api/chat/v3/reset`

Current API route inventory only retains `GET /api/profiles/search` for public profile search.

## Database Notes

No destructive database changes were made. No chatbot conversation/message tables were removed. The `api_usage_logs` table and model were preserved because they are reusable AI usage/cost infrastructure.

Recommendation: keep `api_usage_logs`. If AI usage tracking is removed later, use a new forward cleanup migration rather than deleting historical migrations.

## Verification

Completed successfully:

- Baseline `git status --short` review
- Stale chatbot documentation inventory scan
- `php -l routes/api.php`
- `php -l bootstrap/app.php`
- `php -l config/logging.php`
- `php -l app/Services/AI/DeepSeekClient.php`
- `php -l app/Services/AI/ApiUsageTracker.php`
- `php artisan route:list` with no `/api/chat/*` routes
- `php artisan about`
- `npm.cmd run build`
- Broad `rg` scans for chatbot routes, classes, widget names, route names, middleware aliases, and old service namespaces

Full test suite status:

- `php artisan test` completed with non-chatbot failures: 566 tests, 490 passed, 32 failed, 44 errors.
- Main failure areas: review factories/policies/routes, missing `password.changed` middleware binding, `ProfileStats::recalculate()`, review moderation methods, scheduler/admin seeder expectations, and `App\Http\Controllers\Public\DB` import usage.
- No remaining failure was caused by missing chatbot routes or deleted chatbot classes after the cleanup scans.

## Risks And Warnings

- Any external client still calling `/api/chat/*` now receives a 404.
- Cached views may need clearing in long-running local environments if stale markup appears.
- Historical logs may still contain old chatbot entries; log files were not deleted.
- Unrelated working-tree changes were intentionally left untouched.
