<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Services\ImageOptimizationService;

class OptimizedImage extends Component
{
    public string $src;
    public ?string $webpSrc;
    public ?string $alt;
    public ?string $class;
    public ?string $size;
    public bool $lazy;
    public ?string $placeholder;
    public ?int $width;
    public ?int $height;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $src,
        ?string $alt = null,
        ?string $class = null,
        ?string $size = null,
        bool $lazy = true,
        ?int $width = null,
        ?int $height = null
    ) {
        $this->alt = $alt ?? '';
        $this->class = $class;
        $this->size = $size;
        $this->lazy = $lazy;
        $this->width = $width;
        $this->height = $height;

        // Optimized URL'leri al
        $optimizer = app(ImageOptimizationService::class);
        
        // Size varsa thumbnail kullan
        if ($size) {
            $pathInfo = pathinfo($src);
            $this->src = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . "_{$size}." . $pathInfo['extension'];
        } else {
            $this->src = $src;
        }

        // WebP versiyonu
        $pathInfo = pathinfo($this->src);
        $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
        $this->webpSrc = file_exists(public_path($webpPath)) || 
                         \Storage::disk('public')->exists(str_replace('/storage/', '', $webpPath))
                         ? $webpPath : null;

        // Lazy loading placeholder
        $this->placeholder = $lazy ? $optimizer->createPlaceholder($src) : null;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.optimized-image');
    }
}
