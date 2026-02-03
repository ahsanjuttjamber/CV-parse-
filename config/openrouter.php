<?php

return [

    'api_key' => env('OPENROUTER_API_KEY'),

    'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),

    'default_model' => env('OPENROUTER_DEFAULT_MODEL', 'openai/gpt-4o-mini'),

];
