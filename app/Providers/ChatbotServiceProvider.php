<?php

namespace App\Providers;

use App\Services\Chatbot\CategoryResolverService;
use App\Services\Chatbot\ChatOrchestratorService;
use App\Services\Chatbot\ChatResponseFormatterService;
use App\Services\Chatbot\ChatSafetyService;
use App\Services\Chatbot\CityAliasResolver;
use App\Services\Chatbot\CityResolverService;
use App\Services\Chatbot\ConversationStateManager;
use App\Services\Chatbot\CostTracker;
use App\Services\Chatbot\DeepSeekClient;
use App\Services\Chatbot\DeepSeekConversationService;
use App\Services\Chatbot\Dialects\DialectNormalizer;
use App\Services\Chatbot\IntentDetectionService;
use App\Services\Chatbot\IntentExtractionService;
use App\Services\Chatbot\OutputValidator;
use App\Services\Chatbot\ProviderSearchForChatService;
use App\Services\Chatbot\SafeIntentExtractor;
use Illuminate\Support\ServiceProvider;

class ChatbotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CityResolverService::class);
        $this->app->singleton(CityAliasResolver::class);
        $this->app->singleton(CategoryResolverService::class);
        $this->app->singleton(ChatSafetyService::class);
        $this->app->singleton(IntentDetectionService::class);
        $this->app->singleton(IntentExtractionService::class);
        $this->app->singleton(DialectNormalizer::class);
        $this->app->singleton(SafeIntentExtractor::class);
        $this->app->singleton(CostTracker::class);
        $this->app->singleton(OutputValidator::class);
        $this->app->singleton(ProviderSearchForChatService::class);
        $this->app->singleton(DeepSeekClient::class);
        $this->app->singleton(DeepSeekConversationService::class);
        $this->app->singleton(ChatResponseFormatterService::class);
        $this->app->singleton(ConversationStateManager::class);
        $this->app->singleton(ChatOrchestratorService::class);
    }
}
