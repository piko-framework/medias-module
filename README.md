# Piko medias module

Medias management module for [Piko](https://piko-framework.github.io/) based projects.

This module uses the UI Component [Upload Manager](https://github.com/ilhooq/upload-manager)

![Upload Manager UI](https://github.com/ilhooq/upload-manager/raw/main/example/screenshot.png);

## Features

- **Upload Manager UI integration**: ships with a ready-to-use widget (`UploadManagerWidget`) and bundled JS/CSS assets.
- **Admin-protected media API**: all upload-manager endpoints are restricted to users with `manage.medias` permission.
- **Complete media workflow**: upload, list, update metadata (caption/order), reorder, and delete media through dedicated routes.
- **Entity-aware media grouping**: files are organized by `category` and `ref_id` to attach medias to any domain entity.
- **Secure upload handling**: sanitized filenames + configurable upload size limit via `maxFileSize`.
- **On-demand thumbnail generation**: automatic thumbnail caching for images with configurable `thumbnailQuality` and JPEG/WebP/AVIF output.
- **Automatic filesystem cleanup**: deleting a media record also removes its physical file.
- **Database-backed persistence**: each uploaded file is stored on disk and tracked in the `media` table.
- **Built-in installer command**: includes SQL install scripts and CLI setup (`./vendor/bin/medias-module setup:install`).
- **I18n-ready API messages**: validation and method errors can be translated through the `medias` domain.

## Installation

1 - Install module via composer:

```bash
composer require piko/medias-module
```

2 - Edit your Piko config :

```php
[
  'modules' => [
    // ...
    'medias' => [
      'class' => 'Piko\MediasModule',
      'maxFileSize' => 10 * 1024 * 1024, // 10Mo
      'thumbnailQuality' => 95,
      'managePermission' => 'admin',
    ],
  ],
  'bootstrap' => ['medias'],
]
```

3 - Install the media table.

```bash
export DSN=mysql:host=127.0.0.1;dbname=yourdatabase;charset=utf8mb4
export DB_USERNAME=mysqluser
export DB_PASSWORD=yourpassword

./vendor/bin/medias-module setup:install
```

## Usage in a PHP view template

Example in a Piko view file (for example `views/post/edit.php`):

```views/post/edit.php#L1-26
<?php

use Piko\MediasModule\UploadManagerWidget;

/** @var \Piko\View $this */
/** @var array{id:int} $post */
?>

<h2>Post medias</h2>

<div id="post-medias-uploader"></div>

<?php
UploadManagerWidget::createUI($this, 'post-medias-uploader', [
    'refId' => (int) $post['id'],
    'category' => 'post',
    'destDir' => '@webroot/medias/posts',
    'clientOptions' => [
        'locale' => 'fr',
        'maxFileSize' => 10 * 1024 * 1024,
    ],
]);
?>
```

> `refId` should be the related entity ID, and `category` lets you isolate medias by context (e.g. `post`, `product`, `user`).

## Routes
- **/medias/upload-manager/upload** : POST - file upload endpoint
- **/medias/upload-manager/list** : GET - list medias
- **/medias/upload-manager/update** : PATCH - update media
- **/medias/upload-manager/reorder** : POST - reorder medias
- **/medias/upload-manager/delete** : DELETE - delete a media
