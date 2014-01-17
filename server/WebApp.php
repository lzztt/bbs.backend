<?php

namespace site;

use lzx\App;
use lzx\core\Handler;
use lzx\db\DB;
use lzx\db\MySQL;
use lzx\core\Request;
use lzx\core\Session;
use lzx\core\Cookie;
use lzx\core\Cache;
use lzx\html\Template;
use site\Config;
use site\SessionHandler;

/**
 *
 * @property lzx\core\MySQL $db
 * @property lzx\core\Cache $cache
 * @property lzx\core\Request $request
 * @property lzx\core\Session $session
 * @property lzx\core\Logger $logger
 * @property site\Config $config
 */
// cookie->uid
// cookie->urole
// cookie->umode
// session->uid
// session->urole
require_once \dirname( __DIR__ ) . '/lib/lzx/App.php';

class WebApp extends App
{

    protected $config;

    public function __construct()
    {
        parent::__construct();
        // register current namespaces
        $this->loader->registerNamespace( __NAMESPACE__, __DIR__ );

        // load configuration
        $this->config = new Config();
        // display errors on page if not in production stage
        Handler::$displayError = ($this->config->stage !== Config::STAGE_PRODUCTION);

        $this->setLogDir( $this->config->path['log'] );
        $this->setLogMailer( $this->config->webmaster );

        // enable cache if cache not set and not in developemtn stage
        if ( \is_null( $this->config->cache ) )
        {
            $this->config->cache = ($this->config->stage !== Config::STAGE_DEVELOPMENT);
        }

        // website is offline
        if ( $this->config->mode === Config::MODE_OFFLINE )
        {
            $offline_file = $this->config->path['file'] . '/offline.txt';
            $output = \is_file( $offline_file ) ? \file_get_contents( $offline_file ) : 'Website is currently offline. Please visit later.';
            // page exit
            \header( 'Content-Type: text/html; charset=UTF-8' );
            exit( $output );
        }
    }

    // only controller will handle all exceptions and local languages
    // other classes will report status to controller
    // controller set status back the WebApp object
    // WebApp object will call Theme to display the content
    public function run( $argc = 0, Array $argv = [] )
    {
        $request = $this->getRequest();

        if ( !$this->validateRequest( $request ) )
        {
            $request->pageNotFound();
        }

        $db = MySQL::getInstance( MySQL::DEFAULT_TAG, $this->config->db );

        if ( $this->config->stage !== Config::STAGE_PRODUCTION ) // DEV or TEST stage
        {
            $db->debugMode = TRUE;
        }

        // get cookie
        $cookie = $this->getCookie();

        // start session
        $session = $this->getSession( $cookie );

        // set user info for logger
        $userinfo = 'uid=' . $session->uid
            . ' umode=' . $this->getUmode( $cookie )
            . ' urole=' . (isset( $cookie->urole ) ? $cookie->urole : 'guest');
        $this->logger->setUserInfo( $userinfo );

        // set request uid based on session uid
        $request->uid = \intval( $session->uid );

        // start cache
        $cache = Cache::getInstance( $this->config->path['cache'] );
        $cache->setLogger( $this->logger );
        $cache->setStatus( $this->config->cache );

        // start template
        Template::setLogger( $this->logger );
        Template::$theme = $this->config->theme['default'];
        Template::$path = $this->config->path['theme'];
        $html = new Template( 'html' );
        $html->var['domain'] = $this->config->domain;

        try
        {
            $ctrler = $this->getController( $request );
        }
        catch ( \Exception $e )
        {
            $request->pageNotFound( $e->getMessage() );
        }

        $ctrler->path = $this->config->path;
        $ctrler->logger = $this->logger;
        $ctrler->cache = $cache;
        $ctrler->html = $html;
        $ctrler->request = $request;
        $ctrler->session = $session;
        $ctrler->cookie = $cookie;
        $ctrler->config = $this->config;
        $ctrler->run();
        $session->close();

        $html = (string) $html;

        // output page content
        \header( 'Content-Type: text/html; charset=UTF-8' );
        echo $html;
        \flush();

        if ( Template::getStatus() === TRUE )
        {
            $cache->storePage( $html );
        }

        if ( $this->config->stage !== 'production' ) // DEV or TEST stage
        {
            echo '<pre>' . $request->datetime . \PHP_EOL . $this->config->stage . \PHP_EOL;
            echo $userinfo . \PHP_EOL;
            echo \print_r( MySQL::$queries, TRUE ) . '</pre>';
        }
    }

    // we don't ban IPs
    public function validateRequest( Request $request )
    {
        // ban! invalid $_GET[]
        if ( \sizeof( $this->config->getkeys ) > 0 )
        {
            foreach ( \array_keys( $request->get ) as $key )
            {
                if ( !\in_array( $key, $this->config->getkeys ) )
                {
                    $umode = $this->getUmode( $this->getCookie() );
                    if ( $umode === Template::UMODE_ROBOT )
                    {
                        $this->logger->warn( 'unsupport GET key:' . $key );
                    }
                    else
                    {
                        $this->logger->error( 'unsupport GET key:' . $key );
                    }
                    return FALSE;
                }
            }
        }

        //valid request
        return TRUE;
    }

    /**
     * @param Cookie $cookie
     */
    private function getUmode( Cookie $cookie )
    {
        static $umode;

        if ( !isset( $umode ) )
        {
            $umode = $cookie->umode;

            if ( !\in_array( $umode, [Template::UMODE_PC, Template::UMODE_MOBILE, Template::UMODE_ROBOT] ) )
            {
                $agent = $_SERVER['HTTP_USER_AGENT'];
                if ( \preg_match( '/(http|Yahoo|bot)/i', $agent ) )
                {
                    $umode = Template::UMODE_ROBOT;
                    //}if ($http_user_agent ~ '(http|Yahoo|bot)') {
                }
                elseif ( \preg_match( '/(iPhone|Android|BlackBerry)/i', $agent ) )
                {
                    // if ($http_user_agent ~ '(iPhone|Android|BlackBerry)') {
                    $umode = Template::UMODE_MOBILE;
                }
                else
                {
                    $umode = Template::UMODE_PC;
                }
                $cookie->umode = $umode;
            }
        }

        return $umode;
    }

    /**
     *
     * @return Request
     */
    public function getRequest()
    {
        $req = Request::getInstance();
        if ( !isset( $req->language ) )
        {
            $req->language = $this->config->language;
        }
        return $req;
    }

    /**
     *
     * @return Session
     */
    public function getSession( Cookie $cookie )
    {
        static $session;

        if ( !isset( $session ) )
        {
            $umode = $this->getUmode( $cookie );

            if ( $umode == Template::UMODE_ROBOT )
            {
                $handler = NULL;
            }
            else
            {
                $lifetime = $this->config->cookie['lifetime'];
                $path = $this->config->cookie['path'] ? $this->config->cookie['path'] : '/';
                $domain = $this->config->cookie['domain'] ? $this->config->cookie['domain'] : $this->config->domain;
                \session_set_cookie_params( $lifetime, $path, $domain );
                \session_name( 'LZXSID' );
                $handler = new SessionHandler( DB::getInstance() );
            }
            $session = Session::getInstance( $handler );

            if ( $cookie->uid != $session->uid )
            {
                $cookie->clear();
                $session->clear();
            }
        }

        return $session;
    }

    /**
     * 
     * @staticvar type $cookie
     * @return Cookie
     */
    public function getCookie()
    {
        static $cookie;

        if ( !isset( $cookie ) )
        {
            $lifetime = $this->config->cookie['lifetime'];
            $path = $this->config->cookie['path'] ? $this->config->cookie['path'] : '/';
            $domain = $this->config->cookie['domain'] ? $this->config->cookie['domain'] : $this->config->domain;
            Cookie::setParams( $lifetime, $path, $domain );

            $cookie = Cookie::getInstance();

            // check cookie for robot agent
            $umode = $this->getUmode( $cookie );
            if ( $umode == Template::UMODE_ROBOT && ($cookie->uid != 0 || isset( $cookie->urole )) )
            {
                $cookie->uid = 0;
                unset( $cookie->urole );
            }

            // check role for guest
            if ( $cookie->uid == 0 && isset( $cookie->urole ) )
            {
                unset( $cookie->urole );
            }
        }

        return $cookie;
    }

    /**
     *
     * @param Request $request
     * @return \lzx\core\Controller
     * @throws \Exception
     */
    public function getController( Request $request )
    {
        $ctrler = $request->args[0];
        require_once $this->config->path['server'] . '/route.php';

        if ( \array_key_exists( $ctrler, $route ) )
        {
            $ctrlerClass = $route[$ctrler];
            return new $ctrlerClass( $request->language, $this->config->path['language'] );
        }
        else
        {
            throw new \Exception( 'controller not found :(' );
        }
    }

}

//__END_OF_FILE__