<?php
namespace Piko;

use Piko;
use Nette\Utils\Image;
use Nette\Utils\ImageType;

class MediasModule extends \Piko\Module
{
    public int $maxFileSize = 5242880; // 5 * 1024 * 1024 = 5Mo

    public function bootstrap(): void
    {
        $view = $this->application->getComponent('Piko\View');
        assert($view instanceof \Piko\View);

        $view->attachBehavior('getThumbnail', function ($file, $width = 80, $height = 60, $type = 'jpg')
        {
            $file = Piko::getAlias($file);

            if (!file_exists($file)) {
                return '';
            }

            $thumb = 'thumbnails/' . md5_file($file) . '-' .  $width . 'x' . $height . '.' . $type;

            if (!file_exists(Piko::getAlias('@webroot/' . $thumb))) {

                $imgType = match($type) {
                  'avif' => ImageType::AVIF,
                  'webp' => ImageType::WEBP,
                  default => ImageType::JPEG
                };

                $img = Image::fromFile($file);
                $img->resize($width, $height, Image::Cover);
                $img->save(Piko::getAlias('@webroot/' . $thumb), null, $imgType);
            }

            return Piko::getAlias('@web/' . $thumb);
        });
    }
}
