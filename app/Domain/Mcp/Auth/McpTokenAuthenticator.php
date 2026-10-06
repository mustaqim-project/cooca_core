<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Auth;

use App\Models\BusinessMembership;
use App\Models\McpAccessToken;
use App\Support\Context;

final class McpTokenAuthenticator
{
    /**
     * Authenticate a raw MCP bearer token and bind active business context.
     */
    public function authenticate(string $rawToken): ?McpAccessToken
    {
        $token = trim($rawToken);
        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        if ($token === '') {
            return null;
        }

        $hash = hash('sha256', $token);

        /** @var McpAccessToken|null $accessToken */
        $accessToken = McpAccessToken::query()
            ->with(['business', 'user'])
            ->where('token_hash', $hash)
            ->first();

        if ($accessToken === null || ! $accessToken->isValid()) {
            return null;
        }

        $business = $accessToken->business;
        $user = $accessToken->user;

        if ($business === null || $user === null) {
            return null;
        }

        // Resolve membership and bind Context
        $membership = BusinessMembership::query()
            ->where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->first();

        Context::setBusiness($business, $membership);

        $accessToken->markAsUsed();

        return $accessToken;
    }
}
