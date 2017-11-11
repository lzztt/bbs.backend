<?php

namespace site\handler\api\file;

use site\Service;
use site\Config;
use lzx\core\Response;
use site\dbobject\Image;

class Handler extends Service
{
    public function post()
    {
        if ($this->request->uid == 0) { // we simply don't allow guest to post this form
            $this->error('upload_err_permission_denied');
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

        // use iframe and html to return the JSON result
        $this->response->type = Response::HTML;
    }
}

//__END_OF_FILE__
