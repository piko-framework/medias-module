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

namespace Piko\MediasModule\Behaviors;

use Piko\MediasModule\Contracts\ThumbnailBehaviorInterface;
use Nette\Utils\Image;
use Nette\Utils\ImageType;

/**
 * ViewBehaviors class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class ViewBehaviors implements ThumbnailBehaviorInterface
{
    public function __construct(private int $thumbnailQuality = 90)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getThumbnail(
        string $file,
        int|null $width = 80,
        int|null $height = 60,
        string $type = 'jpg'
    ): string {
        $file = \Piko::getAlias($file);

        if (!file_exists($file)) {
            return '';
        }

        $thumb = 'thumbnails/' . md5_file($file)
               . '-' . ( $width ? $width : 'auto')
               . 'x' . ( $height ? $height : 'auto')
               . '.' . $type;

        if (!file_exists(\Piko::getAlias('@webroot/' . $thumb))) {

            $imgType = match ($type) {
                'avif' => ImageType::AVIF,
                'webp' => ImageType::WEBP,
                default => ImageType::JPEG
            };

            $img = Image::fromFile($file);
            $img->resize($width, $height, $height ? Image::Cover : Image::OrBigger);
            $img->save(\Piko::getAlias('@webroot/' . $thumb), $this->thumbnailQuality, $imgType);
        }

        return \Piko::getAlias('@web/' . $thumb);
    }
}
