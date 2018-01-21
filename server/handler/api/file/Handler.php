<?php declare(strict_types=1);

namespace site\handler\api\file;

use lzx\core\Response;
use site\Config;
use site\Service;
use site\dbobject\Image;

class Handler extends Service
{
    public function post(): void
    {
        if ($this->request->uid == 0) {
            $this->forbidden();
        } elseif (empty($this->request->files)) {
            $this->error('upload_err_no_file');
        } else {
            $fobj = new Image();
            $config = Config::getInstance();
            $imgConf = $config->image;
            $imgConf['path'] = $config->path['file'];
            $imgConf['prefix'] = $this->request->timestamp . $this->request->uid;
            $res = $fobj->saveFile($this->request->files, $imgConf);
        }

        if (is_string($res)) {
            $res = ['error' => $res];
        }

        $this->json($res);
    }

    protected function json(array $return = null): void
    {
        $this->response->type = Response::HTML;
        $this->response->setContent(json_encode($return, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
