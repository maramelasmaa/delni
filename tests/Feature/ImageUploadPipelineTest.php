<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ProfileImageService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ImageUploadPipelineTest extends TestCase
{
    use LazilyRefreshDatabase;

    private Profile $profile;

    private User $provider;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->provider = User::factory()->create();
        $this->provider->assignRole('provider');

        $this->profile = Profile::factory()->create(['user_id' => $this->provider->id]);
    }

    public function test_avatar_upload_stores_webp_file_publicly(): void
    {
        Storage::fake('public');

        $service = app(ProfileImageService::class);
        $file = UploadedFile::fake()->image('avatar.png', 600, 600)->size(2000);

        $path = $service->storeAvatar($file);

        $this->assertNotNull($path);
        $this->assertStringEndsWith('.webp', $path);
        $this->assertTrue(Storage::disk('public')->exists($path));
        $this->assertEquals('public', Storage::disk('public')->getVisibility($path));
    }

    public function test_cover_upload_stores_webp_file_publicly(): void
    {
        Storage::fake('public');

        $service = app(ProfileImageService::class);
        $file = UploadedFile::fake()->image('cover.jpg', 1600, 900)->size(3000);

        $path = $service->storeGalleryImage($file);

        $this->assertNotNull($path);
        $this->assertStringEndsWith('.webp', $path);
        $this->assertTrue(Storage::disk('public')->exists($path));
        $this->assertEquals('public', Storage::disk('public')->getVisibility($path));
    }

    public function test_portfolio_upload_stores_webp_file_publicly(): void
    {
        Storage::fake('public');

        $service = app(ProfileImageService::class);
        $file = UploadedFile::fake()->image('portfolio.png', 1200, 800)->size(3000);

        $path = $service->storePortfolioImage($file);

        $this->assertNotNull($path);
        $this->assertStringEndsWith('.webp', $path);
        $this->assertTrue(Storage::disk('public')->exists($path));
        $this->assertEquals('public', Storage::disk('public')->getVisibility($path));
    }

    public function test_storage_url_generates_valid_public_url(): void
    {
        Storage::fake('public');

        $service = app(ProfileImageService::class);
        $file = UploadedFile::fake()->image('test.jpg')->size(2000);
        $path = $service->storeAvatar($file);

        $url = Storage::disk('public')->url($path);

        $this->assertStringContainsString('/storage/', $url);
        $this->assertStringContainsString('.webp', $url);
        $this->assertStringNotContainsString('storage/storage', $url);
    }

    public function test_replace_image_deletes_old_file(): void
    {
        Storage::fake('public');

        $service = app(ProfileImageService::class);

        $oldFile = UploadedFile::fake()->image('old.png')->size(2000);
        $oldPath = $service->storeAvatar($oldFile);
        $this->assertTrue(Storage::disk('public')->exists($oldPath));

        $newFile = UploadedFile::fake()->image('new.jpg')->size(2000);
        $newPath = $service->replaceImage($oldPath, $newFile, 'avatar');

        $this->assertTrue(Storage::disk('public')->exists($newPath));
        $this->assertFalse(Storage::disk('public')->exists($oldPath));
    }

    public function test_delete_image_removes_file(): void
    {
        Storage::fake('public');

        $service = app(ProfileImageService::class);
        $file = UploadedFile::fake()->image('delete.jpg')->size(2000);
        $path = $service->storeAvatar($file);

        $this->assertTrue(Storage::disk('public')->exists($path));

        $service->deleteImage($path);

        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_public_provider_page_renders_portfolio_images(): void
    {
        $this->profile->update(['is_complete' => true]);

        Subscription::factory()->create([
            'user_id' => $this->provider->id,
            'is_active' => true,
        ]);

        ProfileStats::factory()->create(['profile_id' => $this->profile->id]);

        $portfolio = $this->profile->portfolioItems()->create([
            'title' => 'Test Project',
            'short_description' => 'Test',
            'description' => 'Test',
            'is_active' => true,
        ]);

        $portfolio->images()->create([
            'path' => 'portfolio/images/test-uuid-1234.webp',
            'alt' => 'Test Image',
            'sort_order' => 1,
        ]);

        $this->get("/providers/{$this->profile->slug}")
            ->assertStatus(200)
            ->assertSee('/storage/portfolio/images/test-uuid-1234.webp');
    }

    public function test_avatar_respects_size_limit(): void
    {
        Storage::fake('public');

        $service = app(ProfileImageService::class);
        $oversizedFile = UploadedFile::fake()->image('huge.jpg')->size(5000000);

        $this->expectException(ValidationException::class);

        $service->storeAvatar($oversizedFile);
    }

    public function test_cover_respects_size_limit(): void
    {
        Storage::fake('public');

        $service = app(ProfileImageService::class);
        $oversizedFile = UploadedFile::fake()->image('huge.jpg')->size(5000000);

        $this->expectException(ValidationException::class);

        $service->storeGalleryImage($oversizedFile);
    }

    public function test_invalid_mime_type_rejected(): void
    {
        Storage::fake('public');

        $service = app(ProfileImageService::class);
        $invalidFile = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $this->expectException(ValidationException::class);

        $service->storeAvatar($invalidFile);
    }

    public function test_database_stores_correct_path_format(): void
    {
        $this->profile->update([
            'logo' => 'profiles/avatars/test-uuid.webp',
            'cover_image' => 'profiles/covers/test-uuid.webp',
        ]);

        $this->profile->refresh();

        $this->assertEquals('profiles/avatars/test-uuid.webp', $this->profile->logo);
        $this->assertEquals('profiles/covers/test-uuid.webp', $this->profile->cover_image);
        $this->assertStringNotContainsString('storage/', $this->profile->logo);
        $this->assertStringNotContainsString('storage/', $this->profile->cover_image);
    }
}
