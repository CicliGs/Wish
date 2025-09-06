<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\CacheService;
use App\Services\CacheType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class CacheServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @var CacheService $cacheService */
    protected CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = app(CacheService::class);
    }

    /** @test */
    public function it_can_cache_static_content()
    {
        $key = 'test_static_content';
        $content = '<h1>Test Content</h1>';
        
        $result = $this->cacheService->cacheStaticContent($key, $content);
        
        $this->assertTrue($result);
        $this->assertEquals($content, $this->cacheService->getStaticContent($key));
    }

    /** @test */
    public function it_can_cache_image_data()
    {
        $key = 'test_image';
        $imageData = ['path' => '/images/test.jpg', 'alt' => 'Test Image'];
        
        $result = $this->cacheService->cacheImage($key, $imageData);
        
        $this->assertTrue($result);
        $this->assertEquals($imageData, $this->cacheService->getImage($key));
    }

    /** @test */
    public function it_can_cache_asset_data()
    {
        $key = 'test_css';
        $assetData = ['path' => '/css/test.css', 'version' => '1.0.0'];
        
        $result = $this->cacheService->cacheAsset($key, $assetData);
        
        $this->assertTrue($result);
        $this->assertEquals($assetData, $this->cacheService->getAsset($key));
    }

    /** @test */
    public function it_can_cache_user_avatar()
    {
        $userId = 123;
        $avatarData = ['path' => '/avatars/user123.jpg', 'size' => '150x150'];
        
        $result = $this->cacheService->cacheAvatar($userId, $avatarData);
        
        $this->assertTrue($result);
        $this->assertEquals($avatarData, $this->cacheService->getAvatar($userId));
    }

    /** @test */
    public function it_can_check_cache_existence()
    {
        $key = 'test_existence';
        $content = 'Test content';
        
        $this->assertFalse($this->cacheService->hasCache(CacheType::STATIC_CONTENT, $key));
        
        $this->cacheService->cacheStaticContent($key, $content);
        
        $this->assertTrue($this->cacheService->hasCache(CacheType::STATIC_CONTENT, $key));
    }

    /** @test */
    public function it_can_get_cache_ttl()
    {
        $key = 'test_ttl';
        $content = 'Test content';
        
        $this->cacheService->cacheStaticContent($key, $content);
        
        $ttl = $this->cacheService->getCacheTTL(CacheType::STATIC_CONTENT, $key);
        
        if (config('cache.default') === 'array') {
            $this->assertNull($ttl);
        } else {
            $this->assertNotNull($ttl);
            $this->assertGreaterThan(0, $ttl);
        }
    }

    /** @test */
    public function it_can_clear_cache_by_type()
    {
        $this->cacheService->cacheStaticContent('test1', 'content1');
        $this->cacheService->cacheImage('test2', ['path' => '/test.jpg']);
        
        $this->assertTrue($this->cacheService->hasCache(CacheType::STATIC_CONTENT, 'test1'));
        $this->assertTrue($this->cacheService->hasCache(CacheType::IMAGES, 'test2'));
        
        $result = $this->cacheService->clearCacheByType(CacheType::STATIC_CONTENT);
        
        $this->assertTrue($result);
        $this->assertFalse($this->cacheService->hasCache(CacheType::STATIC_CONTENT, 'test1'));
        $this->assertTrue($this->cacheService->hasCache(CacheType::IMAGES, 'test2'));
    }

    /** @test */
    public function it_can_clear_user_cache()
    {
        $userId = 123;
        
        $this->cacheService->cacheStaticContent("user_{$userId}_profile", 'profile data');
        $this->cacheService->cacheStaticContent("user_{$userId}_wishes", 'wishes data');
        
        $this->assertTrue($this->cacheService->hasCache(CacheType::STATIC_CONTENT, "user_{$userId}_profile"));
        $this->assertTrue($this->cacheService->hasCache(CacheType::STATIC_CONTENT, "user_{$userId}_wishes"));
        
        $result = $this->cacheService->clearUserCache($userId);
        
        $this->assertTrue($result);
        $this->assertFalse($this->cacheService->hasCache(CacheType::STATIC_CONTENT, "user_{$userId}_profile"));
        $this->assertFalse($this->cacheService->hasCache(CacheType::STATIC_CONTENT, "user_{$userId}_wishes"));
    }

    /** @test */
    public function it_can_get_cache_statistics()
    {
        $stats = $this->cacheService->getCacheStats();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('driver', $stats);
        $this->assertArrayHasKey('store', $stats);
        $this->assertArrayHasKey('prefix', $stats);
        $this->assertArrayHasKey('ttl_settings', $stats);
        $this->assertArrayHasKey('description', $stats);
    }

    /** @test */
    public function it_can_clear_all_cache()
    {
        $this->cacheService->cacheStaticContent('test1', 'content1');
        $this->cacheService->cacheImage('test2', ['path' => '/test.jpg']);
        
        $this->assertTrue($this->cacheService->hasCache(CacheType::STATIC_CONTENT, 'test1'));
        $this->assertTrue($this->cacheService->hasCache(CacheType::IMAGES, 'test2'));
        
        $result = $this->cacheService->clearAllCache();
        
        $this->assertTrue($result);
        $this->assertFalse($this->cacheService->hasCache(CacheType::STATIC_CONTENT, 'test1'));
        $this->assertFalse($this->cacheService->hasCache(CacheType::IMAGES, 'test2'));
    }
}
