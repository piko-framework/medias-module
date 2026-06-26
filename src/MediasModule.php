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

namespace Piko;

use Nette\Utils\Image;
use Nette\Utils\ImageType;

/**
 * Medias Module class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class MediasModule extends \Piko\Module
{
    /**
     * Maximum alowed size to upload a file
     *
     * @var integer
     */
    public int $maxFileSize = 5242880; // 5 * 1024 * 1024 = 5Mo

    public function bootstrap(): void
    {
        $i18n =  $this->application->getComponent('Piko\I18n');
        assert($i18n instanceof I18n);
        $i18n->addTranslation('medias', __DIR__ . '/messages');

        $view = $this->application->getComponent('Piko\View');
        assert($view instanceof View);

        $view->attachBehavior('getThumbnail', function ($file, $width = 80, $height = 60, $type = 'jpg') {
            $file = \Piko::getAlias($file);

            if (!file_exists($file)) {
                return '';
            }

            $thumb = 'thumbnails/' . md5_file($file) . '-' . $width . 'x' . $height . '.' . $type;

            if (!file_exists(\Piko::getAlias('@webroot/' . $thumb))) {

                $imgType = match ($type) {
                    'avif' => ImageType::AVIF,
                    'webp' => ImageType::WEBP,
                    default => ImageType::JPEG
                };

                $img = Image::fromFile($file);
                $img->resize($width, $height, Image::Cover);
                $img->save(\Piko::getAlias('@webroot/' . $thumb), null, $imgType);
            }

            return \Piko::getAlias('@web/' . $thumb);
        });
    }
}
