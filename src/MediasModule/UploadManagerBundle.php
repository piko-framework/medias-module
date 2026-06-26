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

namespace Piko\MediasModule;

use Piko\AssetBundle;

/**
 * This class bundle the upload manager UI
 *
 * See : https://github.com/ilhooq/upload-manager
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class UploadManagerBundle extends AssetBundle
{
    /**
     * @var string
     * @see AssetBundle::$name
     */
    public $name = 'upload-manager';

    /**
     * @var string
     * @see AssetBundle::$sourcePath
     */
    public $sourcePath = __DIR__ . '/../../assets';

    /**
     * @var array<string>
     * @see AssetBundle::$js
     */
    public $js = [
        'upload-manager.umd.js',
    ];

    /**
     * @var array<string>
     * @see AssetBundle::$css
     */
    public $css = [
        'upload-manager.css',
    ];
}
