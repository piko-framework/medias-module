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

use Piko\View;

/**
 * Upload Manger Widget class
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class UploadManagerWidget
{
    /**
     * Builds and registers the upload manager UI widget.
     *
     * This method prepares backend endpoints, merges default client options with
     * user-provided options, then initializes the JavaScript {@code UploaderWidget}
     * on DOM ready.
     *
     * @param View   $view   The view instance used to generate URLs and register JavaScript.
     * @param string $id     The DOM element ID where the uploader widget will be mounted.
     * @param array  $params Optional configuration:
     *                       - refId (int): Related entity identifier (default: 0)
     *                       - destDir (string): Destination directory for uploads (default: "@webroot/medias")
     *                       - category (string): Media category (default: "@webroot/medias")
     *                       - clientOptions (array): Additional/overridden client-side widget options
     *                         see https://github.com/ilhooq/upload-manager
     *
     * @return void
     */
    public static function createUI(View $view, string $id, array $params = []): void
    {
        $refId = $params['refId'] ?? 0;
        $destDir = $params['destDir'] ?? '@webroot/medias';
        $category = $params['category'] ?? '@webroot/medias';
        $clientOptions = $params['clientOptions'] ?? [];
        $uploadEndpoint = $view->getUrl('medias/upload-manager/upload', [
          'ref_id' => $refId,
          'category' => $category,
          'dest_dir' => $destDir,
        ]);
        $listEndpoint = $view->getUrl('medias/upload-manager/list', [
          'ref_id' => $refId,
          'category' => $category,
        ]);
        $updateEndpoint = $view->getUrl('medias/upload-manager/update');
        $orderEndPoint = $view->getUrl('medias/upload-manager/reorder');
        $deletEndPoint = $view->getUrl('medias/upload-manager/delete');
        $defaultOptions = [
            'endpoint' => $uploadEndpoint,  // POST  — upload
            'orderEndpoint' => $orderEndPoint, // POST  — reorder
            'listEndpoint' => $listEndpoint,    // GET   — list existing files
            'updateEndpoint' => $updateEndpoint,        // PATCH — update caption / order
            'deleteEndpoint' => $deletEndPoint,  // DELETE?id=... — remove a file
            'fieldName' => 'file',
            'maxFileSize' => 5 * 1024 * 1024, // 5Mo
            'locale' => 'en',
            'onUploadSuccess' =>  '({ file, serverId }) => {
                console.log("uploaded", file.name, "->", serverId)
            }',
            'onUploadError' => '({file, error, response}) => {
                const serverResp = JSON.parse(response.responseText);
                alert(serverResp.error);
            }',
            'onErrorAddFile' => '({file, error, state}) => {
                alert(error.message);
            }'
        ];
        $clientOptions = array_merge($defaultOptions, $clientOptions);

        $callbackKeys = [
            'onReady',
            'onChange',
            'onUploadSuccess',
            'onUploadError',
            'onFileUpdate',
            'onFileUpdateError',
            'onReorder',
            'onDeleteSuccess',
            'onDeleteError',
            'onErrorAddFile'
        ];
        $callbacks = [];

        foreach ($callbackKeys as $key) {
            if (isset($clientOptions[$key]) && is_string($clientOptions[$key])) {
                $callbacks[$key] = $clientOptions[$key];
                unset($clientOptions[$key]);
            }
        }

        $optionsJson = json_encode($clientOptions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $callbacksJs = '';

        foreach ($callbacks as $key => $fnJs) {
            $callbacksJs .= "options.{$key} = {$fnJs};\n";
        }

        $js = "const options = {$optionsJson};\n"
            . $callbacksJs
            . 'const widget = new UploaderWidget("#' . $id . '", options);';

        $view->registerJs("\nwindow.addEventListener('DOMContentLoaded', () => {\n$js\n});");
        UploadManagerBundle::register($view);
    }
}
