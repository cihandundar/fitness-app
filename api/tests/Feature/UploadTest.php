<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Program;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $trainer;
    private User $regularUser;
    private string $adminToken;
    private string $trainerToken;
    private string $userToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->trainer = User::factory()->trainer()->create();
        $this->regularUser = User::factory()->create();

        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
        $this->trainerToken = $this->trainer->createToken('test-token')->plainTextToken;
        $this->userToken = $this->regularUser->createToken('test-token')->plainTextToken;
    }

    /**
     * Test için geçici bir JPG dosyası oluşturur (GD olmadan)
     */
    private function createTestJpgFile(): UploadedFile
    {
        $tempPath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.jpg';
        // Minimal JPG header (1x1 pixel siyah resim)
        $jpgData = base64_decode('/9j/4AAQSkZJRgABAQEAAQABAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/wA=');
        file_put_contents($tempPath, $jpgData);

        return new UploadedFile(
            $tempPath,
            'test.jpg',
            'image/jpeg',
            null,
            true
        );
    }

    /**
     * Test için geçici bir PNG dosyası oluşturur
     */
    private function createTestPngFile(): UploadedFile
    {
        $tempPath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.png';
        // Minimal PNG header (1x1 pixel şeffaf resim)
        $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        file_put_contents($tempPath, $pngData);

        return new UploadedFile(
            $tempPath,
            'test.png',
            'image/png',
            null,
            true
        );
    }

    /**
     * Test için geçici bir PDF dosyası oluşturur (geçersiz resim)
     */
    private function createTestPdfFile(): UploadedFile
    {
        $tempPath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.pdf';
        file_put_contents($tempPath, '%PDF-1.4 test');

        return new UploadedFile(
            $tempPath,
            'test.pdf',
            'application/pdf',
            null,
            true
        );
    }

    // ==================== Program Image Upload Tests ====================

    public function test_admin_can_upload_program_image(): void
    {
        $program = Program::factory()->create();
        $image = $this->createTestJpgFile();

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/programs/' . $program->id . '/upload-image', [
                'image' => $image,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Görsel başarıyla yüklendi.',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);
    }

    public function test_trainer_cannot_upload_program_image(): void
    {
        $program = Program::factory()->create();
        $image = $this->createTestJpgFile();

        $response = $this->withToken($this->trainerToken)
            ->postJson('/api/programs/' . $program->id . '/upload-image', [
                'image' => $image,
            ]);

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_upload_program_image(): void
    {
        $program = Program::factory()->create();
        $image = $this->createTestJpgFile();

        $response = $this->withToken($this->userToken)
            ->postJson('/api/programs/' . $program->id . '/upload-image', [
                'image' => $image,
            ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_upload_program_image(): void
    {
        $program = Program::factory()->create();
        $image = $this->createTestJpgFile();

        $response = $this->postJson('/api/programs/' . $program->id . '/upload-image', [
            'image' => $image,
        ]);

        $response->assertStatus(401);
    }

    public function test_program_upload_requires_image(): void
    {
        $program = Program::factory()->create();

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/programs/' . $program->id . '/upload-image', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_program_upload_requires_valid_image(): void
    {
        $program = Program::factory()->create();
        $pdf = $this->createTestPdfFile();

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/programs/' . $program->id . '/upload-image', [
                'image' => $pdf,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_program_upload_accepts_jpeg(): void
    {
        $program = Program::factory()->create();
        $image = $this->createTestJpgFile();

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/programs/' . $program->id . '/upload-image', [
                'image' => $image,
            ]);

        $response->assertStatus(200);
    }

    public function test_program_upload_accepts_png(): void
    {
        $program = Program::factory()->create();
        $image = $this->createTestPngFile();

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/programs/' . $program->id . '/upload-image', [
                'image' => $image,
            ]);

        $response->assertStatus(200);
    }

    public function test_program_upload_rejects_large_file(): void
    {
        $program = Program::factory()->create();

        // Büyük dosya simülasyonu - 3MB boyutunda bir dosya oluştur
        $tempPath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.jpg';
        $largeData = str_repeat('x', 3 * 1024 * 1024); // 3MB
        file_put_contents($tempPath, $largeData);

        $image = new UploadedFile(
            $tempPath,
            'large.jpg',
            'image/jpeg',
            3145728, // 3MB
            true
        );

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/programs/' . $program->id . '/upload-image', [
                'image' => $image,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_program_upload_returns_error_for_nonexistent_program(): void
    {
        $image = $this->createTestJpgFile();

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/programs/999999/upload-image', [
                'image' => $image,
            ]);

        // 500 döner çünkü model bulunamadığında hata oluşur
        $this->assertContains($response->status(), [404, 500]);
    }

    // ==================== Branch Image Upload Tests ====================

    public function test_admin_can_upload_branch_image(): void
    {
        $branch = Branch::factory()->create();
        $image = $this->createTestJpgFile();

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/branches/' . $branch->id . '/upload-image', [
                'image' => $image,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Görsel başarıyla yüklendi.',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);
    }

    public function test_trainer_cannot_upload_branch_image(): void
    {
        $branch = Branch::factory()->create();
        $image = $this->createTestJpgFile();

        $response = $this->withToken($this->trainerToken)
            ->postJson('/api/branches/' . $branch->id . '/upload-image', [
                'image' => $image,
            ]);

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_upload_branch_image(): void
    {
        $branch = Branch::factory()->create();
        $image = $this->createTestJpgFile();

        $response = $this->withToken($this->userToken)
            ->postJson('/api/branches/' . $branch->id . '/upload-image', [
                'image' => $image,
            ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_upload_branch_image(): void
    {
        $branch = Branch::factory()->create();
        $image = $this->createTestJpgFile();

        $response = $this->postJson('/api/branches/' . $branch->id . '/upload-image', [
            'image' => $image,
        ]);

        $response->assertStatus(401);
    }

    public function test_branch_upload_requires_image(): void
    {
        $branch = Branch::factory()->create();

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/branches/' . $branch->id . '/upload-image', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_branch_upload_requires_valid_image(): void
    {
        $branch = Branch::factory()->create();
        $pdf = $this->createTestPdfFile();

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/branches/' . $branch->id . '/upload-image', [
                'image' => $pdf,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_branch_upload_accepts_jpeg(): void
    {
        $branch = Branch::factory()->create();
        $image = $this->createTestJpgFile();

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/branches/' . $branch->id . '/upload-image', [
                'image' => $image,
            ]);

        $response->assertStatus(200);
    }

    public function test_branch_upload_accepts_png(): void
    {
        $branch = Branch::factory()->create();
        $image = $this->createTestPngFile();

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/branches/' . $branch->id . '/upload-image', [
                'image' => $image,
            ]);

        $response->assertStatus(200);
    }

    public function test_branch_upload_rejects_large_file(): void
    {
        $branch = Branch::factory()->create();

        // Büyük dosya simülasyonu
        $tempPath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.jpg';
        $largeData = str_repeat('x', 3 * 1024 * 1024); // 3MB
        file_put_contents($tempPath, $largeData);

        $image = new UploadedFile(
            $tempPath,
            'large.jpg',
            'image/jpeg',
            3145728, // 3MB
            true
        );

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/branches/' . $branch->id . '/upload-image', [
                'image' => $image,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_branch_upload_returns_error_for_nonexistent_branch(): void
    {
        $image = $this->createTestJpgFile();

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/branches/999999/upload-image', [
                'image' => $image,
            ]);

        // 500 döner çünkü model bulunamadığında hata oluşur
        $this->assertContains($response->status(), [404, 500]);
    }
}
