<?php declare(strict_types=1);

namespace site\dbobject;

use Exception;
use Imagick;
use lzx\db\DBObject;
use lzx\db\DB;
use lzx\core\Logger;

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

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'images';
        parent::__construct($db, $table, $id, $properties);
    }

    private function rmTmpFile($file)
    {
        try {
            unlink($file);
        } catch (Exception $e) {
            $logger = Logger::getInstance();
            $logger->error($e->getMessage() . ' : ' . $file);
        }
    }

    // will always assuming multiple file array
    public function saveFile(array $files, array $config)
    {
        $errmsg = [
            UPLOAD_ERR_INI_SIZE    => 'upload_err_ini_size',
            UPLOAD_ERR_FORM_SIZE  => 'upload_err_form_size',
            UPLOAD_ERR_PARTIAL     => 'upload_err_partial',
            UPLOAD_ERR_NO_FILE     => 'upload_err_no_file',
            UPLOAD_ERR_NO_TMP_DIR => 'upload_err_no_tmp_dir',
            UPLOAD_ERR_CANT_WRITE => 'upload_err_cant_write',
            102                          => 'upload_err_invalid_type',
            103                          => 'upload_err_cant_save',
        ];

        $errorFile = [];
        $savedFile = [];
        // save files
        foreach ($files as $type => $fileList) {
            $path = $config['path'] . '/data/' . $type . '/' . $config['prefix'] . rand(0, 9);

            foreach ($fileList as $i => $f) {
                $fileName = $f['name'];
                $tmpFile = $f['tmp_name'];

                // check upload error
                if ($f['error'] !== UPLOAD_ERR_OK) { // upload error
                    $errorFile[] = [
                        'name'  => $fileName,
                        'error' => $errmsg[$f['error']],
                    ];
                    if ($tmpFile) {
                        $this->rmTmpFile($tmpFile);
                    }
                    continue;
                }

                // check image size
                if ($f['size'] > $config['size']) { // File Size
                    $errorFile[] = [
                        'name'  => $fileName,
                        'error' => $errmsg[UPLOAD_ERR_INI_SIZE],
                    ];
                    if ($tmpFile) {
                        $this->rmTmpFile($tmpFile);
                    }
                    continue;
                }

                $imageInfo = getimagesize($tmpFile); // not requiring GD
                // check image type
                if ($imageInfo === false || !in_array($imageInfo[2], $config['types'])) {
                    $errorFile[] = [
                        'name'  => $fileName,
                        'error' => $errmsg[102],
                    ];
                    if ($tmpFile) {
                        $this->rmTmpFile($tmpFile);
                    }
                    continue;
                }

                $savePath = $path . $i . image_type_to_extension($imageInfo[2], true); // not requiring GD
                $width = $imageInfo[0];
                $height = $imageInfo[1];

                // save image
                try {
                    if ($width > $config['width'] || $height > $config['height']) {
                        // resize image
                        $im = new Imagick($tmpFile);
                        $this->autoRotateImage($im);
                        $im->resizeImage($config['width'], $config['height'], Imagick::FILTER_LANCZOS, 1, true);
                        $im->writeImage($savePath);
                        $im->clear();
                        if ($tmpFile) {
                            $this->rmTmpFile($tmpFile);
                        }
                    } else {
                        // copy image
                        move_uploaded_file($tmpFile, $savePath);
                    }
                } catch (Exception $e) {
                    if (isset($im)) {
                        $im->clear();
                        unset($im);
                    }
                    if ($tmpFile) {
                        $this->rmTmpFile($tmpFile);
                    }
                    $logger = Logger::getInstance();
                    $logger->error($e->getMessage());
                    $errorFile[] = [
                        'name'  => $fileName,
                        'error' => $errmsg[103],
                    ];
                    continue;
                }

                $p = strrpos($fileName, '.');

                $uri = substr($savePath, strlen($config['path']));
                $savedFile[] = [
                    'name' => $p > 0 ? substr($fileName, 0, $p) : $fileName,
                    'path' => $uri
                ];
                $this->call('image_add_tmp("' . $uri . '")');
            }
        }

        return ['error' => $errorFile, 'saved' => $savedFile];
    }

    private function autoRotateImage(Imagick $img)
    {
        $orientation = $img->getImageOrientation();

        switch ($orientation) {
            case Imagick::ORIENTATION_BOTTOMRIGHT:
                $img->rotateimage("#000", 180); // rotate 180 degrees
                $img->setImageOrientation(Imagick::ORIENTATION_TOPLEFT); // update the EXIF data
                break;
            case Imagick::ORIENTATION_RIGHTTOP:
                $img->rotateimage("#000", 90); // rotate 90 degrees CW
                $img->setImageOrientation(Imagick::ORIENTATION_TOPLEFT); // update the EXIF data
                break;
            case Imagick::ORIENTATION_LEFTBOTTOM:
                $img->rotateimage("#000", -90); // rotate 90 degrees CCW
                $img->setImageOrientation(Imagick::ORIENTATION_TOPLEFT); // update the EXIF data
                break;
        }
    }

    public function addImages(array $files, $filePath, $nid, $cid = null)
    {
        foreach ($files as $file) {
            if ($file['action'] == 'add') {
                $info = getimagesize($filePath . $file['path']);
                $width = $info[0];
                $height = $info[1];
                $this->call('image_add(:nid, :cid, :name, :path, :height, :width, :city_id)', [
                    ':nid'      => $nid,
                    ':cid'      => $cid,
                    ':name'     => $file['name'],
                    ':path'     => $file['path'],
                    ':height'  => $height,
                    ':width'    => $width,
                    ':city_id' => $this->cityId]);
            }
        }
    }

    public function updateImages(array $files, $filePath, $nid, $cid = null)
    {
        if (sizeof($files) > 0) {
            $deletedIDs = [];

            foreach ($files as $file) {
                switch ($file["action"]) {
                    case 'add':
                        $info = getimagesize($filePath . $file['path']);
                        $width = $info[0];
                        $height = $info[1];
                        $this->call('image_add(:nid, :cid, :name, :path, :height, :width, :city_id)', [
                            ':nid'      => $nid,
                            ':cid'      => $cid,
                            ':name'     => $file['name'],
                            ':path'     => $file['path'],
                            ':height'  => $height,
                            ':width'    => $width,
                            ':city_id' => $this->cityId]);
                        break;
                    case 'update':
                        $this->call('image_update(:fid, :name)', [':fid' => $file['id'], ':name' => $file['name']]);
                        break;
                    case 'delete':
                        if ($file['id'] > 0) {
                            $deletedIDs[] = $file['id'];
                        }
                        break;
                    default:
                        continue;
                }
            }

            if (sizeof($deletedIDs) > 0) {
                $this->call('image_delete("' . implode(',', $deletedIDs) . '")');
            }
        }

        $image = new Image();
        $image->nid = $nid;
        $image->cid = $cid;
        return $image->getList('id,name,path');
    }

    public function updateFileList(array $files, $filePath, $nid, $cid = null)
    {
        $nid = (int) $nid;
        if ($cid) { // comment
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
                    $this->call('image_add(:nid, :cid, :name, :path, :height, :width, :city_id)', [':nid'      => $nid,
                        ':cid'      => $cid,
                        ':name'     => $file['name'],
                        ':path'     => $file['path'],
                        ':height'  => $height,
                        ':width'    => $width,
                        ':city_id' => $this->cityId]);
                } catch (Exception $e) {
                    $logger = Logger::getInstance();
                    $logger->error($e->getMessage());
                    continue;
                }
            }
        }
        // delete old saved files are not in new version
        if ($images) {
            $this->call('image_delete("' . implode(',', array_keys($images)) . '")');
        }
    }

    public function getRecentImages($city_id)
    {
        return $this->call('get_recent_images(' . $city_id . ')');
    }
}
