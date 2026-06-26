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

use Piko\MediasModule\Behaviors\ViewBehaviors;

/**
 * Medias Module class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class MediasModule extends \Piko\Module
{
    /**
     * Controller namespace
     *
     * @var string
     */
    public $controllerNamespace = 'Piko\\MediasModule\\Controllers';

    /**
     * Maximum alowed size to upload a file
     *
     * @var integer
     */
    public int $maxFileSize = 5242880; // 5 * 1024 * 1024 = 5Mo

    /**
     * Module management permission
     *
     * @var string
     */
    public string $managePermission = 'manage.medias';

    /**
     * Thumbnail quality from 0 to 100
     *
     * @var integer
     */
    public int $thumbnailQuality = 85;

    public function bootstrap(): void
    {
        $i18n =  $this->application->getComponent('Piko\I18n');
        assert($i18n instanceof I18n);
        $i18n->addTranslation('medias', __DIR__ . '/messages');

        $view = $this->application->getComponent('Piko\View');
        assert($view instanceof View);

        $viewBehaviors = new ViewBehaviors($this->thumbnailQuality);
        $view->attachBehavior('getThumbnail', [$viewBehaviors, 'getThumbnail']);
    }
}
