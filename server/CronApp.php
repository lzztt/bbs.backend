<?php

declare(strict_types=1);

namespace site;

use Laminas\Diactoros\ServerRequestFactory;
use lzx\App;
use lzx\core\Request;
use lzx\core\Response;
use lzx\db\DB;
use site\Config;

class CronApp extends App
{
    protected $timestamp;
    protected $config;
    protected $actions;

    public function __construct()
    {
        parent::__construct();

        $this->config = Config::getInstance();
        $this->logger->setFile($this->config->path['log'] . '/' . $this->config->domain . '.log');
        $this->logger->setEmail($this->config->webmaster, 'web error: cron', 'logger@' . $this->config->domain);
        $this->logger->addExtraInfo(['user' => 'cron']);
    }

    public function run(array $args): void
    {
        $db = DB::getInstance($this->config->db);
        $request = new Request(ServerRequestFactory::fromGlobals());
        $session = new Session(false);
        $handler = new CronHandler($request, new Response(), $this->config, $this->logger, $session, []);
        $handler->run();
    }
}
