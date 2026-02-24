<?php

namespace App\Services;

use Meilisearch\Client;
use Meilisearch\Contracts\CancelTasksQuery;
use Meilisearch\Contracts\DeleteTasksQuery;

final class MeiliSearchServices
{
    public static function getClient(): Client
    {
        return new Client(
            config('scout.meilisearch.host'),
            config('scout.meilisearch.key')
        );
    }

    public static function updateEmbedders(): array
    {
        return self::getClient()->index('products')->updateEmbedders([
            'default' => [
                'source' => 'openAi',
                'model' => 'text-embedding-3-small',
                'apiKey' => config('openai.OPENAI_API_KEY'),
                'documentTemplate' => 'A product used in construction titled "{{doc.product_name}}"',
            ]
        ]);
    }

    public static function resetEmbedders(): array
    {
        return self::getClient()->index('products')->resetEmbedders();
    }
    
    public static function cancelTasks(array $statuses): array
    {
        return self::getClient()->cancelTasks((new CancelTasksQuery())->setStatuses($statuses));
    }

    public static function deleteTasks(array $statuses): array
    {
        return self::getClient()->deleteTasks((new DeleteTasksQuery())->setStatuses($statuses));
    }
}
