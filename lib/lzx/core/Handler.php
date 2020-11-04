<?php

declare(strict_types=1);

namespace lzx\core;

use Exception;
use lzx\core\Logger;
use lzx\core\Request;
use lzx\core\Response;
use lzx\core\UtilTrait;
use lzx\exception\NotFound;

abstract class Handler
{
    use UtilTrait;

    public $logger;
    public $request;
    public $response;

    public function __construct(Request $req, Response $response, Logger $logger)
    {
        $this->request = $req;
        $this->response = $response;
        $this->logger = $logger;
    }

    abstract public function run(): void;

    public function afterRun(): void
    {
    }

    protected function json(array $return = null): void
    {
        $this->response->setContent($return ? $return : (object) null);
    }

    protected function getPagerInfo(int $nTotal, int $nPerPage): array
    {
        if ($nPerPage <= 0) {
            throw new Exception('invalid value for number of items per page: ' . $nPerPage);
        }

        $pageCount = $nTotal > 0 ? (int) ceil($nTotal / $nPerPage) : 1;
        if (array_key_exists('p', $this->request->data)) {
            if ($this->request->data['p'] === 'l') {
                $pageNo = $pageCount;
            } elseif (is_numeric($this->request->data['p'])) {
                $pageNo = (int) $this->request->data['p'];

                if ($pageNo < 1 || $pageNo > $pageCount) {
                    throw new NotFound();
                }
            } else {
                throw new NotFound();
            }
        } else {
            $pageNo = 1;
        }

        return [$pageNo, $pageCount];
    }
}
