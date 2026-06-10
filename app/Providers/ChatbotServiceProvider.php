<?php

namespace App\Providers;

use App\Services\Chatbot\CategoryResolverService;
use App\Services\Chatbot\ChatContextBuilderService;
use App\Services\Chatbot\ChatOrchestratorService;
use App\Services\Chatbot\ChatPromptBuilderService;
use App\Services\Chatbot\ChatResponseFormatterService;
use App\Services\Chatbot\ChatSafetyService;
use App\Services\Chatbot\CityAliasResolver;
use App\Services\Chatbot\CityResolverService;
use App\Services\Chatbot\ConversationStateManager;
use App\Services\Chatbot\DeepSeekClient;
use App\Services\Chatbot\IntentDetectionService;
use App\Services\Chatbot\ProviderMatchScorer;
use App\Services\Chatbot\ProviderSearchForChatService;
use App\Services\Chatbot\ServiceIntentExtractor;
use App\Services\Chatbot\SmartProviderMatcher;
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
        $this->app->singleton(ProviderSearchForChatService::class);
        $this->app->singleton(ChatContextBuilderService::class);
        $this->app->singleton(ChatPromptBuilderService::class);
        $this->app->singleton(DeepSeekClient::class);
        $this->app->singleton(ChatResponseFormatterService::class);
        $this->app->singleton(ServiceIntentExtractor::class);
        $this->app->singleton(ProviderMatchScorer::class);
        $this->app->singleton(SmartProviderMatcher::class);
        $this->app->singleton(ConversationStateManager::class);
        $this->app->singleton(ChatOrchestratorService::class);
    }
}
