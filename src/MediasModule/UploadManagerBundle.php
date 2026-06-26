<?php

namespace Piko\MediasModule;

use Piko\AssetBundle;

class UploadManagerBundle extends AssetBundle
{
    public $name = 'upload-manager';

    public $sourcePath = __DIR__ . '/../../assets';

    public $js = [
        'upload-manager.umd.js',
    ];

    public $css = [
        'upload-manager.css',
    ];
}
