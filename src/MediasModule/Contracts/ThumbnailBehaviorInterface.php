<?php

/**
 * This file is part of the Piko user module
 *
 * @package Piko\MediasModule
 * @copyright 2026 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/medias-module
 */

declare(strict_types=1);

namespace Piko\MediasModule\Contracts;

/**
 * This interface can be used to prevent errors like missing method with LSP
 *
 * You can use it like this in your views templates :
 * `// @var \Piko\View&Piko\MediasModule\Contracts\ThumbnailBehaviorInterface $this`
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
interface ThumbnailBehaviorInterface
{
    /**
     * Generates a thumbnail URL for a given file.
     *
     * This method creates a thumbnail by resizing the specified file to the given width and height.
     * The output format can be specified using the $type parameter (e.g., 'jpg', 'png', 'webp').
     *
     * @param string $file The path or identifier of the file for which to generate the thumbnail.
     * @param int|null $width The desired width of the thumbnail in pixels. Defaults to 80.
     * @param int|null $height The desired height of the thumbnail in pixels. Defaults to 60.
     * @param string $type The output format of the thumbnail (e.g., 'jpg', 'png'). Defaults to 'jpg'.
     *
     * @return string The URL or path to the generated thumbnail.
     */
    public function getThumbnail(
        string $file,
        int|null $width = 80,
        int|null $height = 60,
        string $type = 'jpg'
    ): string;
}
