<?php

use App\Services\PexelsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

test('PexelsService returns empty array when API key is not configured', function () {
    Config::set('services.pexels.api_key', '');

    $service = new PexelsService();
    $images = $service->getBackgroundImages();

    expect($images)->toBeArray()->toBeEmpty();
});

test('PexelsService caches API responses', function () {
    Config::set('services.pexels.api_key', 'test-api-key');

    // Mock HTTP response
    Http::fake([
        'api.pexels.com/*' => Http::response([
            'photos' => [
                [
                    'id' => 1,
                    'src' => [
                        'original' => 'https://example.com/original.jpg',
                        'large' => 'https://example.com/large.jpg',
                        'small' => 'https://example.com/small.jpg',
                    ],
                    'photographer' => 'Test Photographer',
                    'photographer_url' => 'https://example.com/photographer',
                    'alt' => 'Test image',
                ],
            ],
        ], 200),
    ]);

    // Clear cache before test
    Cache::flush();

    $service = new PexelsService();

    // First call should hit the API
    $images = $service->getBackgroundImages(1);

    expect($images)->toBeArray()
        ->toHaveCount(1)
        ->and($images[0])->toHaveKeys(['id', 'url', 'thumbnail', 'photographer', 'photographer_url', 'alt']);

    // Second call should use cache
    $cachedImages = $service->getBackgroundImages(1);
    expect($cachedImages)->toEqual($images);

    // Verify HTTP was only called once
    Http::assertSentCount(1);
});

test('PexelsService formats image data correctly', function () {
    Config::set('services.pexels.api_key', 'test-api-key');

    Http::fake([
        'api.pexels.com/*' => Http::response([
            'photos' => [
                [
                    'id' => 123,
                    'src' => [
                        'original' => 'https://example.com/original.jpg',
                        'large' => 'https://example.com/large.jpg',
                        'small' => 'https://example.com/small.jpg',
                    ],
                    'photographer' => 'John Doe',
                    'photographer_url' => 'https://example.com/johndoe',
                    'alt' => 'Beautiful landscape',
                ],
            ],
        ], 200),
    ]);

    Cache::flush();
    $service = new PexelsService();
    $images = $service->getBackgroundImages(1);

    expect($images[0])
        ->id->toBe(123)
        ->url->toBe('https://example.com/large.jpg')
        ->thumbnail->toBe('https://example.com/small.jpg')
        ->photographer->toBe('John Doe')
        ->photographer_url->toBe('https://example.com/johndoe')
        ->alt->toBe('Beautiful landscape');
});

test('PexelsService returns empty array on API failure', function () {
    Config::set('services.pexels.api_key', 'test-api-key');

    Http::fake([
        'api.pexels.com/*' => Http::response([], 500),
    ]);

    Cache::flush();
    $service = new PexelsService();
    $images = $service->getBackgroundImages();

    expect($images)->toBeArray()->toBeEmpty();
});

test('getRandomBackground returns a random image', function () {
    Config::set('services.pexels.api_key', 'test-api-key');

    Http::fake([
        'api.pexels.com/*' => Http::response([
            'photos' => [
                [
                    'id' => 1,
                    'src' => [
                        'large' => 'https://example.com/large1.jpg',
                        'small' => 'https://example.com/small1.jpg',
                    ],
                    'photographer' => 'Test 1',
                    'photographer_url' => 'https://example.com/test1',
                ],
                [
                    'id' => 2,
                    'src' => [
                        'large' => 'https://example.com/large2.jpg',
                        'small' => 'https://example.com/small2.jpg',
                    ],
                    'photographer' => 'Test 2',
                    'photographer_url' => 'https://example.com/test2',
                ],
            ],
        ], 200),
    ]);

    Cache::flush();
    $service = new PexelsService();
    $image = $service->getRandomBackground();

    expect($image)
        ->toBeArray()
        ->toHaveKeys(['id', 'url', 'thumbnail', 'photographer', 'photographer_url']);
});

test('getRandomBackground returns null when no images available', function () {
    Config::set('services.pexels.api_key', '');

    $service = new PexelsService();
    $image = $service->getRandomBackground();

    expect($image)->toBeNull();
});
