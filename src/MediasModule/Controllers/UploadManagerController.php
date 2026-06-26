<?php
namespace Piko\MediasModule\Controllers;

use Exception;
use PDO;
use Nette\Utils\Image;
use Piko;
use Piko\User;
use Piko\HttpException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use app\modules\medias\models\Media;

class UploadManagerController extends \Piko\Controller
{
    public $layout = false;

    public function __construct(private PDO $db, User $user)
    {
        if (!$user->can('admin')) {
            throw new HttpException(403, 'Not authorized.');
        }
    }

    public function listAction(int $ref_id = 0, string $category = 'image'): ResponseInterface
    {
        if ($this->request->getMethod() !== 'GET') {
            return $this->invalidMethod();
        }

        $rows = Media::find($this->db, ['category' => $category, 'ref_id' => $ref_id], 'sort_order');
        $files = array_map(fn($row) => $this->getFileInfo($row), $rows);

        return $this->jsonResponse(['files' => $files]);
    }

    public function uploadAction(int $ref_id = 0, string $category = 'image', string $dest_dir = '@webroot/medias') : ResponseInterface
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->invalidMethod();
        }

        if (!is_dir(Piko::getAlias($dest_dir))) {
            mkdir(Piko::getAlias($dest_dir), 0755, true);
        }

        $uploads = $this->request->getUploadedFiles();

        if (empty($uploads)) {
            return $this->jsonResponse(['error' => 'Aucun fichier transmis.'])->withStatus(400);
        }

        $savedFiles = [];

        foreach ($uploads as $upload) {
            /* @var \HttpSoft\Message\UploadedFile $upload */

            $originalName = $this->normalizeFileName($upload->getClientFilename());
            $size = $upload->getSize();
            $fileTo = $dest_dir . '/' . $originalName;

            if ($size <= 0) {
                return $this->jsonResponse(['error' => 'Fichier vide.'])->withStatus(400);
            }

            if ($size > $this->module->maxFileSize) {
                return $this->jsonResponse([
                    'error' => sprintf('Fichier trop volumineux (max %s).', $this->formatBytes($this->module->maxFileSize))
                ])->withStatus(400);
            }

            try {
                $upload->moveTo(Piko::getAlias($fileTo));
            } catch (RuntimeException $e) {
                return $this->jsonResponse(['error' => $e->getMessage()])->withStatus(500);
            }

            /* @var Media $media */
            $media = $this->create(Media::class);

            try {
                $media->loadRef($ref_id, $category, $originalName);
            } catch (RuntimeException $e) {
                $media->category = $category;
                $media->ref_id = $ref_id;
            }

            $media->type = $upload->getClientMediaType();
            $media->name = $originalName;
            $media->path = $fileTo;
            $media->save();

            $savedFiles[] = $media;
        }

        return $this->jsonResponse( [
            'id' => $savedFiles[0]->id,
            'file' => $this->getFileInfo($savedFiles[0]),
        ])->withStatus(201);
    }

    public function updateAction(): ResponseInterface
    {
        if ($this->request->getMethod() !== 'PATCH') {
            return $this->invalidMethod();
        }

        $raw = (string) $this->request->getBody();
        $json = json_decode($raw, true);

        if (!is_array($json) || !isset($json['id']) || !is_string($json['id'])) {
            return $this->jsonResponse(['error' => 'Payload invalide: id requis.'])->withStatus(400);
        }

        $changes = $json['changes'] ?? [];

        try {
            /* @var Media $media */
            $media = $this->create(Media::class);
            $media->load((int) $json['id']);
            $media->bind($changes);
            $media->save();

            return $this->jsonResponse( [
                'id' => $media->id,
                'file' => $this->getFileInfo($media),
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()])->withStatus(400);
        }
    }

    public function reorderAction(): ResponseInterface
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->invalidMethod();
        }

        $raw = (string) $this->request->getBody();
        $json = json_decode($raw, true);

        $orders = $json['order'] ?? [];
        $savedOrders = [];

        try {
            foreach ($orders as $order => $id) {
                /* @var Media $media */
                $media = $this->create(Media::class);
                $media->load($id);
                $media->sort_order = $order;
                $media->save();

                $savedOrders[] = (string) $media->id;
            }

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()]);
        }

        return $this->jsonResponse(['order' => $savedOrders]);
    }

    /**
     * Delete a media
     *
     * @param int $id
     */
    public function deleteAction(int $id = 0): ResponseInterface
    {
        if ($this->request->getMethod() !== 'DELETE') {
            return $this->invalidMethod();
        }

        /* @var Media $media */
        $media = $this->create(Media::class);
        $media->load($id);
        $media->delete();

        return $this->jsonResponse(['deleted' => $media->name]);
    }

    protected function invalidMethod(): ResponseInterface
    {
        return $this->jsonResponse(['error' => 'Méthode non autorisée.'])->withStatus(405);
    }

    protected function getFileInfo(Media $media): array
    {
        $url = Piko::getAlias(str_replace('@webroot', '@web', $media->path));
        $thumbnailUrl = '';

        if ($media->type == 'image') {
            $thumbnailUrl = $this->getThumbnail($media);
        }

        return [
            'id' => $media->id,
            'name' => $media->name,
            'size' => file_exists(Piko::getAlias($media->path))? filesize(Piko::getAlias($media->path)) : 0,
            'url' => $thumbnailUrl ? $thumbnailUrl : $url,
            'type' => $media->type,
            'caption' => $media->caption,
            'order' => $media->sort_order,
        ];
    }

    protected function getThumbnail(Media $media, int $width = 80, int $height = 60): string
    {
        $thumb = 'thumbnails/' . $width . 'x' . $height . '-' . $media->id . '-' . $media->name;

        if (!file_exists(Piko::getAlias('@webroot/' . $thumb))) {
            $img = Image::fromFile(Piko::getAlias($media->path));
            $img->resize($width, $height, Image::Cover);
            $img->save(Piko::getAlias('@webroot/' . $thumb));
        }

        return Piko::getAlias('@web/' . $thumb);
    }

    protected function normalizeFileName(string $name): string
    {
        $clean = trim(basename($name));
        $clean = preg_replace('/[^A-Za-z0-9._-]/', '_', $clean) ?? '';
        $clean = ltrim($clean, '.');

        return $clean === '' ? 'file' : $clean;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 o';
        }

        $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $amount = $bytes / pow(1024, $power); // 1024 élevé à la puissance de power
        $hasFraction = abs($amount - round($amount)) > 0.0000001;
        $decimals = ($power === 0 || !$hasFraction) ? 0 : 2;

        return number_format($amount, $decimals, ',', '') . ' ' . $units[$power];
    }
}
