# DELNI CHATBOT V3 - CONVERSATIONAL AI REBUILD

## Architecture: Stateful Conversational Flow

```
USER MESSAGE
    ↓
ChatControllerV3 (validate, load state, rate limit)
    ↓
ChatOrchestratorService (orchestrate flow)
    ├─ Stage 1: Cheap checks (greeting, empty, etc)
    │   └─ Respond deterministically (no AI call)
    │
    ├─ Stage 2: Extract intent + search + respond
    │   ├─ DeepSeekConversationService.chat()
    │   │   └─ Returns: { intent_extracted, response, providers_needed }
    │   ├─ ProviderSearchForChatService.search()
    │   │   └─ Returns: providers[] (max 5)
    │   └─ Format response with DB results
    │
    └─ Update conversation state in cache
         └─ Next message will have full context

RESPONSE (DB-grounded, conversational)
    ↓
Frontend (display message + provider cards)
```

## Token Budget Breakdown

Per message:

1. **Input tokens** (~200):
   - System prompt (~80)
   - Current message (~40)
   - State summary (~50)
   - Max 5 providers (~30)

2. **Output tokens** (~400-500):
   - Natural response in Arabic
   - May reference providers or ask follow-up

3. **Total**: ~600-700 tokens per DeepSeek call
4. **Cost**: ~$0.0002-0.0003 per message
5. **Limit**: 50 messages/day = $0.01-0.015/user/day

## Implementation Checklist

- [ ] ConversationStateService - compact state storage
- [ ] DeepSeekConversationService - conversational wrapper
- [ ] ChatOrchestratorService - orchestration logic
- [ ] ChatControllerV3 - API endpoint
- [ ] Frontend widget - handle all response types
- [ ] Tests - 14 test cases
- [ ] Documentation - system prompt + examples

## Critical Points

1. **State is source of truth** - not full history
2. **Max 5 providers to AI** - truncated to 150 chars
3. **One DeepSeek call max** - for extraction + response
4. **Deterministic fallback** - if DeepSeek fails
5. **Provider names searchable** - "فني زياد" must find provider
6. **Conversation memory** - "Tripoli, 7 years" continues previous search

## Next Steps

1. Write ConversationStateService for cache storage
2. Write DeepSeekConversationService for intelligent conversation
3. Update ChatOrchestratorService with new flow
4. Update ChatControllerV3 with rate limiting + state management
5. Update frontend widget to handle all response types
6. Write comprehensive tests
7. Deploy and monitor token usage

