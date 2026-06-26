# Piko medias module

Medias management module for [Piko](https://piko-framework.github.io/) based projects.

## Features


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

## Routes
- **/medias/upload-manager/upload** : POST - file upload endpoint
- **/medias/upload-manager/list** : GET - list medias
- **/medias/upload-manager/update** : PATCH - update media
- **/medias/upload-manager/reorder** : POST - reorder medias
- **/medias/upload-manager/delete** : DELETE - delete a media
