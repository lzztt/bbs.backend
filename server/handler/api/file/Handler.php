<?php declare(strict_types=1);

namespace site\handler\api\file;

use Imagick;
use lzx\core\Response;
use site\Config;
use site\Service;

class Handler extends Service
{
    const ERR_INVALID_TYPE = 100;
    const ERROR_MESSAGES = [
        UPLOAD_ERR_OK => 'The file uploaded with success.',
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize.',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE in the HTML form.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'An extension stopped the file upload.',
        self::ERR_INVALID_TYPE => 'The type of the uploaded file is not allowed',
    ];

    public function post(): void
    {
        if ($this->request->uid === 0) {
            $this->forbidden();
        }

        $config = Config::getInstance();
        $saveDir = $config->path['file'];
        $saveName = $this->request->timestamp . $this->request->uid;
        $res = $this->saveFiles(self::getUploadFiles(), $saveDir, $saveName, $config->image);
        $this->json($res);
    }

    protected function json(array $return = null): void
    {
        $this->response->type = Response::HTML;
        $this->response->setContent(json_encode($return, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private static function getUploadFiles(): array
    {
        $uploadFiles = [];
        foreach ($_FILES as $field => $file) {
            $uploadFiles[$field] = [];
            if (is_array($file['error'])) {
                foreach (array_keys($file['error']) as $i) {
                    foreach (array_keys($file) as $info) {
                        $uploadFiles[$field][$i][$info] = $file[$info][$i];
                    }
                }
            } else {
                $uploadFiles[$field][] = $file;
            }
        }

        return $uploadFiles;
    }

    private function saveFiles(array $uploadFiles, string $saveDir, string $saveName, array $config): array
    {
        if (!$uploadFiles) {
            return ['error' => self::ERROR_MESSAGES[UPLOAD_ERR_NO_FILE]];
        }

        $fails = [];
        $saved = [];
        foreach ($uploadFiles as $field => $files) {
            $baseUri = '/data/' . $field . '/' . $saveName . rand(0, 9);

            foreach ($files as $i => $file) {
                if (!$this->checkError($file, $fails)) {
                    continue;
                }

                if (!$this->checkSize($file, $fails, $config['size'])) {
                    continue;
                }

                $info = getimagesize($file['tmp_name']);
                if ($info === false || !$this->checkType($files, $fails, $info[2], $config['types'])) {
                    continue;
                }

                $fileUri = $baseUri . $i . image_type_to_extension($info[2], true);
                if (!$this->saveImage($file, $fails, $saveDir . $fileUri, $info[0], $info[1], $config['width'], $config['height'])) {
                    continue;
                }

                $p = strrpos($file['name'], '.');
                $saved[] = [
                    'name' => $p > 0 ? substr($file['name'], 0, $p) : $file['name'],
                    'path' => $fileUri
                ];
            }
        }

        return ['error' => $fails, 'saved' => $saved];
    }

    private function deleteTmpFile(string $file): void
    {
        if (!$file) {
            return;
        }

        try {
            unlink($file);
        } catch (Exception $e) {
            $this->logger->logException($e);
        }
    }

    private function handleFailedFile(array $file, array &$fails, int $error): void
    {
        $fails[] = [
            'name' => $file['name'],
            'error' => self::ERROR_MESSAGES[$error],
        ];

        $this->deleteTmpFile($file['tmp_name']);
    }

    private function checkError(array $file, array &$fails): bool
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->handleFailedFile($file, $fails, $file['error']);
            return false;
        }
        return true;
    }

    private function checkSize(array $file, array &$fails, int $maxSize): bool
    {
        if ($file['size'] > $maxSize) {
            $this->handleFailedFile($file, $fails, UPLOAD_ERR_FORM_SIZE);
            return false;
        }
        return true;
    }

    private function checkType(array $file, array &$fails, int $type, array $allowedTypes): bool
    {
        if (!in_array($type, $allowedTypes)) {
            $this->handleFailedFile($file, $fails, self::ERR_INVALID_TYPE);
            return false;
        }
        return true;
    }

    private function saveImage(array $file, array &$fails, string $savePath, int $width, int $height, int $maxWidth, int $maxHeight): bool
    {
        try {
            if ($width > $maxWidth || $height > $maxHeight) {
                $im = new Imagick($file['tmp_name']);
                self::autoRotateImage($im);
                $im->resizeImage($maxWidth, $maxHeight, Imagick::FILTER_LANCZOS, 1, true);
                $im->writeImage($savePath);
                $im->clear();
                $this->deleteTmpFile($file['tmp_name']);
            } else {
                move_uploaded_file($file['tmp_name'], $savePath);
            }
        } catch (Exception $e) {
            if (isset($im)) {
                $im->clear();
                unset($im);
            }
            $this->handleFailedFile($file, $fails, UPLOAD_ERR_CANT_WRITE);
            $this->logger->logException($e);
            return false;
        }
        return true;
    }


    private static function autoRotateImage(Imagick $img): void
    {
        switch ($img->getImageOrientation()) {
            case Imagick::ORIENTATION_BOTTOMRIGHT:
                $img->rotateimage("#000", 180);
                $img->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
                break;
            case Imagick::ORIENTATION_RIGHTTOP:
                $img->rotateimage("#000", 90);
                $img->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
                break;
            case Imagick::ORIENTATION_LEFTBOTTOM:
                $img->rotateimage("#000", -90);
                $img->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
                break;
        }
    }
}
