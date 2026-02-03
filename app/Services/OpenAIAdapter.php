<?php

namespace App\Services;

class OpenAIAdapter
{
    protected OpenRouterService $client;

    public function __construct(OpenRouterService $client)
    {
        $this->client = $client;
    }

    /**
     * ✅ This makes OpenAI::chat() work
     */
    public function chat()
    {
        return $this;
    }

    /**
     * ✅ This makes OpenAI::chat()->create() work
     */
    public function create(array $data)
    {
        return $this->client->chatCompletion($data);
    }
}
