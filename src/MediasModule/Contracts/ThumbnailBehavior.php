<?php

declare(strict_types=1);

namespace Piko\MediasModule\Contracts;

interface ThumbnailBehavior
{
    /**
     * Generates a thumbnail URL for a given file.
     *
     * This method creates a thumbnail by resizing the specified file to the given width and height.
     * The output format can be specified using the $type parameter (e.g., 'jpg', 'png', 'webp').
     *
     * @param string $file The path or identifier of the file for which to generate the thumbnail.
     * @param int $width The desired width of the thumbnail in pixels. Defaults to 80.
     * @param int $height The desired height of the thumbnail in pixels. Defaults to 60.
     * @param string $type The output format of the thumbnail (e.g., 'jpg', 'png'). Defaults to 'jpg'.
     *
     * @return string The URL or path to the generated thumbnail.
     */
    public function getThumbnail(string $file, int $width = 80, int $height = 60, string $type = 'jpg'): string;
}
