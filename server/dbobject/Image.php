<?php declare(strict_types=1);

namespace site\dbobject;

use Exception;
use lzx\core\Logger;
use lzx\db\DB;
use lzx\db\DBObject;

class Image extends DBObject
{
    public $id;
    public $nid;
    public $cid;
    public $name;
    public $path;
    public $height;
    public $width;
    public $cityId;

    public function __construct(int $id = 0, string $properties = '')
    {
        $db = DB::getInstance();
        $table = 'images';
        parent::__construct($db, $table, $id, $properties);
    }

    public function updateFileList(array $files, string $filePath, int $nid, int $cid = null): void
    {
        if ($cid) {
            $arr = $this->call('get_comment_images(' . $cid . ')');
        } else {
            $arr = $this->call('get_node_images(' . $nid . ')');
        }

        $images = [];
        foreach ($arr as $r) {
            $images[(int) $r['id']] = $r;
        }

        foreach ($files as $fid => $file) {
            if (is_numeric($fid)) {
                // existing image
                $fid = (int) $fid;
                if ($file['name'] != $images[$fid]['name']) {
                    $this->call('image_update(:fid, :name)', [':fid' => $fid, ':name' => $file['name']]);
                }
                unset($images[$fid]);
            } else {
                // new uploaded files
                try {
                    $info = getimagesize($filePath . $file['path']);
                    $width = $info[0];
                    $height = $info[1];
                    $this->call('image_add(:nid, :cid, :name, :path, :height, :width, :city_id)', [
                        ':nid' => $nid,
                        ':cid' => $cid,
                        ':name' => $file['name'],
                        ':path' => $file['path'],
                        ':height' => $height,
                        ':width' => $width,
                        ':city_id' => $this->cityId]);
                } catch (Exception $e) {
                    Logger::getInstance()->logException($e);
                    continue;
                }
            }
        }
        // delete old saved files are not in new version
        if ($images) {
            $this->call('image_delete("' . implode(',', array_keys($images)) . '")');
        }
    }

    public function getRecentImages(int $city_id): array
    {
        return $this->call('get_recent_images(' . $city_id . ')');
    }
}
