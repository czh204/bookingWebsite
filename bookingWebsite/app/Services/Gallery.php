<?php

namespace App\Services;

/**
 * Turns a stored image_path into a URL the page can render.
 *
 * The path comes from the hotels.image_path / attractions.image_path
 * column, so a photo is changed by editing that row — no file renaming.
 * Two forms are accepted:
 *
 *     images/hotels/peninsula.jpg     a file under public/
 *     https://example.com/photo.jpg   a remote image
 *
 * Returns null when the column is empty, or when it names a local file
 * that isn't there. That last case matters: a typo in the column shows
 * the gradient placeholder rather than a broken-image icon.
 */
class Gallery
{
    public function resolve(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Tolerate how a path gets copied out of Windows Explorer:
        // backslashes, a leading slash, and a redundant "public/" prefix
        // are all normalised away. The path is relative to public/, so
        // "public\images\hotels\nyc.webp" means the same file as
        // "images/hotels/nyc.webp".
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        $path = preg_replace('#^public/#', '', $path);

        return file_exists(public_path($path)) ? asset($path) : null;
    }
}
