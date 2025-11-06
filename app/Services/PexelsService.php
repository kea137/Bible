<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PexelsService
{
    private string $apiKey;

    private string $baseUrl = 'https://api.pexels.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.pexels.api_key', env('PEXELS_API_KEY', ''));
    }

    /**
     * Get background images for verse sharing.
     * Uses caching to reduce API calls and stay within rate limits.
     */
    public function getBackgroundImages(int $perPage = 20): array
    {
        if (empty($this->apiKey)) {
            Log::warning('Pexels API key not configured');

            return [];
        }

        // Cache for 1 hour to reduce API calls (200 per hour limit)
        // Use a consistent cache key to ensure we get the same set of images
        $cacheKey = 'pexels_backgrounds_christian_'.md5($perPage);

        return Cache::remember($cacheKey, 3600, function () use ($perPage) {
            try {
                // Use a consistent query for better caching and image consistency
                $query = 'christian nature light happiness';

                $response = Http::withHeaders([
                    'Authorization' => $this->apiKey,
                ])
                    ->timeout(10)
                    ->get("{$this->baseUrl}/search", [
                        'query' => $query,
                        'per_page' => $perPage,
                        'orientation' => 'square', // Best for social media sharing
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    return $this->formatImages($data['photos'] ?? []);
                }

                Log::warning('Pexels API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Error fetching Pexels images', [
                    'error' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }

    /**
     * Format image data for frontend consumption.
     */
    private function formatImages(array $photos): array
    {
        return array_map(function ($photo) {
            return [
                'id' => $photo['id'],
                'url' => $photo['src']['large'] ?? $photo['src']['original'],
                'thumbnail' => $photo['src']['small'] ?? $photo['src']['tiny'],
                'photographer' => $photo['photographer'],
                'photographer_url' => $photo['photographer_url'],
                'alt' => $photo['alt'] ?? 'Beautiful background image',
            ];
        }, $photos);
    }

    /**
     * Get a random background image.
     */
    public function getRandomBackground(): ?array
    {
        $images = $this->getBackgroundImages();

        if (empty($images)) {
            return null;
        }

        return $images[array_rand($images)];
    }
}
