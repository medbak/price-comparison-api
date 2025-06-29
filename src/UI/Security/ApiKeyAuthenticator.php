<?php

declare(strict_types=1);

namespace App\UI\Security;

use Symfony\Component\HttpFoundation\Request;

class ApiKeyAuthenticator
{
    private string $validApiKey;

    public function __construct(string $apiKey = 'your-secret-api-key')
    {
        $this->validApiKey = $apiKey;
    }

    public function authenticate(Request $request): bool
    {
        $apiKey = $request->headers->get('X-API-Key');

        return $apiKey === $this->validApiKey;
    }
}
