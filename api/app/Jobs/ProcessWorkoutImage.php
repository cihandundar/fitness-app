<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;

class ProcessWorkoutImage implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $imagePath,
        public ?string $modelType = null, // 'workout', 'exercise', 'program'
        public ?int $modelId = null
    ) {
        $this->onQueue('images');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Image manager'ı başlat
            $manager = new ImageManager(new Driver());

            // Orijinal dosyayı oku
            $fullPath = Storage::disk('public')->path($this->imagePath);

            if (!file_exists($fullPath)) {
                Log::warning('ProcessWorkoutImage: file not found', ['path' => $this->imagePath]);
                return;
            }

            $image = $manager->read($fullPath);

            // Orijinali koru ve farklı boyutlar oluştur
            $sizes = [
                'thumbnail' => 150,
                'small' => 300,
                'medium' => 600,
                'large' => 1200,
            ];

            foreach ($sizes as $sizeName => $width) {
                $resized = $image->scale($width);
                $newPath = $this->getResizedPath($this->imagePath, $sizeName);

                Storage::disk('public')->put(
                    $newPath,
                    $resized->toJpeg(quality: 85)
                );
            }

            // WebP formatında da kaydet (daha küçük boyut)
            $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $this->imagePath);
            Storage::disk('public')->put(
                $webpPath,
                $image->toWebp(quality: 85)
            );

            Log::info('Workout image processed', [
                'original_path' => $this->imagePath,
                'model_type' => $this->modelType,
                'model_id' => $this->modelId
            ]);

        } catch (Exception $e) {
            Log::error('Failed to process workout image', [
                'image_path' => $this->imagePath,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Resize edilmiş dosya yolunu oluştur
     */
    protected function getResizedPath(string $originalPath, string $size): string
    {
        $pathInfo = pathinfo($originalPath);
        $dirname = $pathInfo['dirname'] ?? '';
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? 'jpg';

        return "{$dirname}/{$filename}_{$size}.{$extension}";
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Workout image processing job failed permanently', [
            'image_path' => $this->imagePath,
            'error' => $exception->getMessage()
        ]);
    }
}
