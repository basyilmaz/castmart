{{-- Optimized Image Component with WebP support and Lazy Loading --}}
{{-- Usage: <x-shop::optimized-image src="/path/to/image.jpg" alt="..." size="medium" /> --}}

<picture>
    @if($webpSrc)
        <source srcset="{{ $webpSrc }}" type="image/webp">
    @endif
    
    <img 
        src="{{ $lazy && $placeholder ? $placeholder : $src }}"
        @if($lazy)
            data-src="{{ $src }}"
            class="lazy {{ $class ?? '' }}"
        @else
            class="{{ $class ?? '' }}"
        @endif
        alt="{{ $alt ?? '' }}"
        @if(isset($width)) width="{{ $width }}" @endif
        @if(isset($height)) height="{{ $height }}" @endif
        loading="{{ $lazy ? 'lazy' : 'eager' }}"
        decoding="async"
    >
</picture>
