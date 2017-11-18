<?php declare(strict_types=1);

namespace site\handler\wedding;

use site\Controller;
use lzx\core\Request;
use lzx\core\Response;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use site\Session;

abstract class Wedding extends Controller
{
    private $register_end = false;

    public function __construct(Request $req, Response $response, Config $config, Logger $logger, Session $session)
    {
        parent::__construct($req, $response, $config, $logger, $session);

        Template::$theme = $this->config->theme['wedding'];

        if ($this->session->loginStatus !== true && file_exists('/tmp/wedding')) {
            $this->register_end = true;
        }
    }

    public function getTableGuests(array $guests, $countField)
    {
        $table_guests = [];
        $table_counts = [];
        $total = 0;
        foreach ($guests as $g) {
            if (!array_key_exists($g['tid'], $table_guests)) {
                $table_guests[$g['tid']] = [];
                $table_counts[$g['tid']] = 0;
            }
            $table_guests[$g['tid']][] = $g;
            $table_counts[$g['tid']] += $g[$countField];
            $total += $g[$countField];
        }

        ksort($table_guests);

        return [$table_guests, $table_counts, $total];
    }

    protected function displayLogin()
    {
        Template::$theme = $this->config->theme['wedding2'];

        $defaultRedirect = '/wedding/listall';
        if ($this->request->referer && $this->request->referer !== '/wedding/login') {
            $this->session->loginRedirect = $this->request->referer;
        } else {
            $this->session->loginRedirect = $defaultRedirect;
        }

        $this->var['body'] = new Template('login', ['uri' => $this->request->uri]);
    }
}
