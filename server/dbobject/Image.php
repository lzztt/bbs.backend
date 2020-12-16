<?php

declare(strict_types=1);

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

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'images', $id, $properties);
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
                if (array_key_exists($fid, $images) && $file['name'] != $images[$fid]['name']) {
                    $this->call('image_update(:fid, :name)', [':fid' => $fid, ':name' => $file['name']]);
                }
                unset($images[$fid]);
            } else {
                // new uploaded files
                try {
                    $this->call('image_add(:nid, :cid, :name, :path, :height, :width, :city_id)', [
                        ':nid' => $nid,
                        ':cid' => $cid,
                        ':name' => $file['name'],
                        ':path' => $file['path'],
                        ':height' => $file['height'],
                        ':width' => $file['width'],
                        ':city_id' => $this->cityId
                    ]);
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
        // explain shows that "where () < 1" is much faster than "group by"
        $sql = '
        SELECT nid, name, path, title
        FROM images AS i
            JOIN nodes AS n ON i.nid = n.id
        WHERE (SELECT count(*) FROM images AS ii WHERE i.nid = ii.nid AND ii.id < i.id) < 1
            AND n.status = 1
            AND i.city_id = ' . $city_id . '
            AND i.width >= 600
            AND i.height >=300
        ORDER BY i.id DESC
        LIMIT 10';
        return $this->db->query($sql);
    }
}
