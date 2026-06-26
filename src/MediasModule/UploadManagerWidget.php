<?php

declare(strict_types=1);

namespace Piko\MediasModule;

use Piko\View;

class UploadManagerWidget
{
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
        $js = 'const widget = new UploaderWidget("#' . $id . '", ' . json_encode($clientOptions, JSON_PRETTY_PRINT) . ');';
        $view->registerJs("\nwindow.addEventListener('DOMContentLoaded', () => {\n$js\n});");
        UploadManagerBundle::register($view);
    }
}
