<?php
namespace app\modules\medias\models;

use PDO;
use Piko;
use Piko\DbRecord;
use Piko\DbRecord\Attribute\Table;
use Piko\DbRecord\Attribute\Column;

/**
 * This is the model class for table "media".
 */
#[Table(name:'media')]
class Media extends DbRecord
{
    #[Column(primaryKey: true)]
    public ?int $id = null;

    #[Column]
    public string $category = '';

    #[Column]
    public ?int $ref_id = 0;

    #[Column]
    public string $type = '';

    #[Column]
    public string $name = '';

    #[Column]
    public ?string $caption = null;

    #[Column]
    public string $path = '';

    #[Column]
    public ?string $created_at = null;

    #[Column]
    public ?string $updated_at = null;

    #[Column]
    public int $sort_order = 0;

    /**
    * Load row data.
    * @param integer $refId The value of the ref id.
    * @param string $category The category name.
    * @param string $fileName The file name.
    *
    * @return void
    * @throws \RuntimeException
    */
    public function loadRef($refId, $category = '', $fileName = ''): void
    {
        $params = [':refId' => $refId];
        $query = 'SELECT * FROM `' . $this->tableName . '` WHERE `ref_id` = :refId';

        if (!empty($category)) {
            $query .= ' AND `category` = :category';
            $params[':category'] = $category;
        }

        if (!empty($fileName)) {
            $query .= ' AND `name` = :fileName';
            $params[':fileName'] = $fileName;
        }

        $st = $this->db->prepare($query);
        $st->execute($params);
        $data = $st->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new \RuntimeException("Error while trying to load item with ref id {$refId}");
        }

        $this->bind($data);
    }

    protected function beforeSave($insert): bool
    {
        if (empty($this->created_at)) {
            $this->created_at = date('Y-m-d H:i:s');
        }

        if (!empty($this->id)) {
            $this->updated_at = date('Y-m-d H:i:s');
        }

        return true;
    }

    public static function find(PDO $db, array $filters = [], string $order = '', int $start = 0, int $limit = 0): array
    {
        $query = 'SELECT * FROM media';

        $where = [];

        if (!empty($filters['ref_id'])) {
            $where[] = 'ref_id = :ref_id';
        }

        if (!empty($filters['category'])) {
            $where[] = 'category = :category';
        }

        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        $query .= ' ORDER BY ' . (empty($order) ? '`id` ASC' : $order);

        if (!empty($start)) {
            $query .= ' OFFSET ' . (int) $start;
        }

        if (!empty($limit)) {
            $query .= ' LIMIT ' . (int) $limit;
        }

        $sth = $db->prepare($query);

        if (!empty($filters['ref_id'])) {
            $sth->bindParam(':ref_id', $filters['ref_id'] , PDO::PARAM_INT);
        }

        if (!empty($filters['category'])) {
            $sth->bindParam(':category', $filters['category'] , PDO::PARAM_STR);
        }

        $sth->execute();

        return $sth->fetchAll(PDO::FETCH_CLASS, static::class, [$db]);
    }

    protected function afterDelete(): void
    {
        if (file_exists(Piko::getAlias($this->path))) {
            unlink(Piko::getAlias($this->path));
        }

        parent::afterDelete();
    }
}
