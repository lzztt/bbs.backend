<?php declare(strict_types=1);

namespace site\handler\api\captcha;

use lzx\core\Response;
use lzx\exception\Forbidden;
use site\Service;
use Gregwar\Captcha\CaptchaBuilder;

class Handler extends Service
{
    public function get(): void
    {
        if (!($this->request->referer && $this->args)) {
            throw new Forbidden();
        }

        $builder = new CaptchaBuilder;
        $builder->build();

        $this->session->set('captcha', $builder->getPhrase());

        $this->response->type = Response::JPEG;
        $this->response->setContent($builder->get());
    }
}
