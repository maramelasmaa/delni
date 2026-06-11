# Delni Chatbot Rebuild Report

Date: 2026-06-11

## Final Architecture

The rebuilt chatbot is a Laravel-grounded conversational assistant. The controller is thin and delegates to focused services:

- `ChatController` accepts `init`, `message`, and `reset` API requests.
- `ChatMessageRequest` validates stable input with a 500-character message limit.
- `ChatbotService` coordinates rate limiting, safety, state, understanding, provider search, and response formatting.
- `ConversationStateService` stores compact state and the last 4 messages in cache.
- `SearchUnderstandingService` extracts service, city, provider/entity query, category, subcategory, and experience hints.
- `ProviderSearchForChatService` performs DB search only through visible profiles and marketplace ranking.
- `ChatContextBuilder` sends compact public-safe context to DeepSeek.
- `DeepSeekConversationService` optionally formats the final response using the generic AI client.
- `ChatResponseBuilder` guarantees the stable frontend response shape.

## DeepSeek Usage Strategy

DeepSeek is used only as an optional conversational formatter. Laravel always decides provider eligibility, matching, ranking, and public-safe fields.

DeepSeek receives only:

- current user message capped to 500 characters
- compact state fields
- last 4 messages
- max 5 providers
- public-safe provider fields
- bios truncated to 120 characters

DeepSeek never receives hidden providers, admin fields, subscription data, API keys, raw prompts, or full profile records.

## Token-Saving Strategy

- Form request rejects messages over 500 characters.
- Conversation state is compact and cached.
- Message memory is capped to the last 4 messages.
- Provider context is capped to 5 results.
- Provider bios are truncated to 120 characters.
- DeepSeek response options are capped to `max_tokens: 450` and `temperature: 0.3`.
- Greetings, reset, safety fallback, and rate-limit responses do not call DeepSeek.

## DB Grounding And Visibility Proof

Provider results come only from `ProviderSearchForChatService`, which builds a `Profile` query joined to `users` and `profile_stats`, then applies:

- `ProfileVisibilityService::applyVisibleQuery()`
- city/category/subcategory/provider/entity filters
- `MarketplaceRankingService::applySearchRanking()`

The chatbot never trusts DeepSeek to choose visible providers. It can only format providers already returned by Laravel.

## Frontend Widget Status

The public layout includes `<x-chatbot-widget />`. The widget provides:

- floating public-site button
- RTL Arabic panel
- message bubbles
- quick suggestions
- loading-safe fetch handling
- provider result cards
- reset conversation
- stable response consumption via `message`, `providers`, `suggested_actions`, `needs`, and `session_id`

The widget is not mounted in Filament admin/provider panels.

## Tests Added And Run

Added `tests/Feature/Chatbot/ChatbotConversationTest.php` covering:

- service query without city asks for city
- service plus city searches immediately
- pending city answer continues previous search
- provider/entity name search
- hidden/inactive providers are not returned
- greeting does not call DeepSeek
- reset clears state

Verification completed:

- `php artisan optimize:clear`
- `php artisan route:list --path=api/chat`
- `npm.cmd run build`
- `php artisan test --compact --filter=Chatbot`

Focused chatbot tests passed: 7 tests, 24 assertions.

## Remaining Risks

- DeepSeek JSON intent extraction is intentionally not used in this first rebuild; deterministic understanding handles the core requested examples and DeepSeek formats responses only after Laravel search.
- Full browser automation could not run because the Node browser runtime hit the Windows sandbox spawn issue.
- The full app test suite still has unrelated review/auth/scheduler failures from outside the chatbot work.

## Final Verdict

The rebuilt Delni chatbot is conversational enough for service, city, follow-up, and provider/entity searches while staying grounded only in the Delni database. Visibility, subscription, suspension, completeness, ranking, and public-safe fields are enforced by Laravel before DeepSeek sees any provider data.
