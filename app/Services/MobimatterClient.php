<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MobimatterClient
{
    public function purchase(string $productId, array $payload): array
    {
        $config = config('services.mobimatter');

        if (empty($config['merchant_id']) || empty($config['api_key'])) {
            throw new RuntimeException('Mobimatter credentials are not configured.');
        }

        if (empty($productId)) {
            throw new RuntimeException('Product ID is missing for this eSIM plan.');
        }

        $endpoint = rtrim($config['base_url'] ?? '', '/') . '/api/v1/orders';

        $response = Http::retry(2, 500)
            ->withHeaders([
                'X-API-KEY' => $config['api_key'],
                'X-MERCHANT-ID' => $config['merchant_id'],
                'Accept' => 'application/json',
            ])
            ->post($endpoint, [
                'productId' => $productId,
                ...$payload,
            ]);

        if ($response->failed()) {
            $body = $response->json() ?: $response->body();
            throw new RuntimeException('Mobimatter API error: ' . (is_string($body) ? $body : json_encode($body)));
        }

        return $response->json() ?? [];
    }
}
