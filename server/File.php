<?php declare(strict_types=1);

namespace site;

use Exception;
use lzx\core\Logger;

class File
{
    const ERR_INVALID_TYPE = 100;
    const ERR_INVALID_WIDTH = 101;
    const ERR_SAVE = 102;
    const ERROR_MESSAGES = [
        UPLOAD_ERR_OK => 'The file uploaded with success.',
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize.',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE in the HTML form.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'An extension stopped the file upload.',
        self::ERR_INVALID_TYPE => 'The type of the uploaded file is not allowed.',
        self::ERR_INVALID_WIDTH => 'The width of the uploaded image is too big.',
        self::ERR_SAVE => 'The file could not be saved.',
    ];

    public static function saveFiles(array $files, string $saveDir, string $saveName, array $config): array
    {
        $fails = [];
        $saved = [];

        foreach ($files as $key => $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                self::handleFailedFile($file, $fails, $file['error']);
                continue;
            }

            if ($file['size'] > $config['size']) {
                self::handleFailedFile($file, $fails, UPLOAD_ERR_FORM_SIZE);
                continue;
            }

            $info = getimagesize($file['tmp_name']);

            if ($info === false) {
                self::handleFailedFile($file, $fails, self::ERR_INVALID_TYPE);
                continue;
            }

            if ($info[0] > $config['width']) {
                self::handleFailedFile($file, $fails, self::ERR_INVALID_WIDTH);
                continue;
            }

            if (!in_array($info[2], $config['types'])) {
                self::handleFailedFile($file, $fails, self::ERR_INVALID_TYPE);
                continue;
            }

            $fileUri = '/data/attachment/' . $saveName . $key . image_type_to_extension($info[2], true);

            try {
                move_uploaded_file($file['tmp_name'], $saveDir . $fileUri);
            } catch (Exception $e) {
                self::handleFailedFile($file, $fails, UPLOAD_ERR_CANT_WRITE);
                Logger::getInstance()->logException($e);
                continue;
            }

            $saved[] = [
                'name' => $file['name'],
                'path' => $fileUri,
                'width' => $info[0],
                'height' => $info[1],
            ];
        }

        return ['error' => $fails, 'saved' => $saved];
    }

    private static function deleteTmpFile(string $file): void
    {
        if (!$file) {
            return;
        }

        try {
            unlink($file);
        } catch (Exception $e) {
            Logger::getInstance()->logException($e);
        }
    }

    private static function handleFailedFile(array $file, array &$fails, int $error): void
    {
        $fails[] = [
            'name' => $file['name'],
            'error' => self::ERROR_MESSAGES[$error],
        ];

        self::deleteTmpFile($file['tmp_name']);
    }
}