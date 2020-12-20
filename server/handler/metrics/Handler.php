<?php

declare(strict_types=1);

namespace site\handler\metrics;

use Laminas\Diactoros\Response\TextResponse;
use lzx\exception\NotFound;
use Prometheus\RenderTextFormat;
use site\Controller;
use site\Metric;

class Handler extends Controller
{
    const LIMIT_ROBOT = -1;

    public function run(): void
    {
        if (!in_array($this->request->ip, $this->config->allowlist)) {
            throw new NotFound();
        }

        $metric = new Metric();
        $renderer = new RenderTextFormat();
        $result = $renderer->render($metric->getMetricFamilySamples());

        $this->response->setResponse(new TextResponse($result, 200, [
            'Content-Type' => RenderTextFormat::MIME_TYPE,
        ]));
    }
}
