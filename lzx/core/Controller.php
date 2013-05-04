<?php

namespace lzx\core;

// only controller will handle all exceptions and local languages
// other classes will report status to controller
// controller set status back the WebApp object
// WebApp object will call Theme to display the content

/**
 *
 * @property \lzx\Core\Cache $cache
 * @property \lzx\core\Logger $logger
 * @property \lzx\html\Template $html
 * @property \lzx\core\Request $request
 * @property Array $path
 * @property \lzx\core\Session $session
 * @property \lzx\core\Cookie $cookie
 *
 */
abstract class Controller
{

   const GUEST_UID = 0;
   const ADMIN_UID = 1;

   public $path;
   public $logger;
   public $cache;
   public $html;
   public $request;
   public $session;
   public $cookie;
   protected static $l = array();
   protected $class;

   public function __construct($lang, $lang_path)
   {
      $this->class = \get_class($this);
      if (!\array_key_exists($this->class, self::$l))
      {
         $lang_file = $lang_path . \str_replace('\\', '/', $this->class) . '.' . $lang . '.php';
         if (\is_file($lang_file))
         {
            include $lang_file;
         }
         self::$l[$this->class] = isset($language) ? $language : array();
      }
   }

   abstract public function run();

   public function checkAJAX()
   {
      $action = 'ajax';
      $args = $this->request->args;

      // ajax request : /<controller>/ajax
      if (\sizeof($args) > 1 && $args[1] == $action)
      {
         $ref_args = $this->request->getURIargs($this->request->referer);
         if (!\in_array($args[0], array($ref_args[0], 'file')))
         {
            $this->request->pageForbidden($this->l('ajax_access_error'));
         }

         if (\method_exists($this, $action))
         {
            try
            {
               $return = $this->$action();
            }
            catch (\Exception $e)
            {
               $this->logger->error($e->getMessage());
               $return['error'] = $this->l('ajax_excution_error');
            }
         }
         else
         {
            $return['error'] = $this->l('ajax_method_not_found');
         }

         // set default response data type
         $type = $this->request->get['type'];
         if (!\in_array($type, array('json', 'html', 'text')))
         {
            $type = 'json';
         }

         if ($type == 'json')
         {
            $return = \json_encode($return);
            if ($return === FALSE)
            {
               $return = array('error' => $this->l('ajax_json_encode_error'));
               $return = \json_encode($return);
            }
         }
         else
         {
            if (\is_array($return))
            {
               if (\sizeof($return) == 1 && \array_key_exists('error', $return))
               {
                  $return = $this->l('Error') . ' : ' . $return['error'];
               }
            }

            if (!\is_string($return))
            {
               $return = $this->l('Error') . ' : ' . $this->l('ajax_data_type_error');
            }
         }

         $this->request->pageExit($return);
      }
   }

   public function runAction($action)
   {
      $func = $action . 'Action';
      if (\method_exists($this, $func))
      {
         return $this->$func();
      }
      else
      {
         $arr = \explode('\\', $this->class);
         $ctrler = array_pop($arr);
         $actionFile = $this->path['root'] . '/Controller/' . $ctrler . 'Action/' . $action . '.php';
         $actionClass = $this->class . 'Action\\' . $action;

         if (!\is_file($actionFile))
         {
            throw new \Exception($this->l('action_not_found') . ' : ' . $action);
         }

         $action = new $actionClass($this->request->language, $this->path['language']);
         $action->path = $this->path;
         $action->logger = $this->logger;
         $action->cache = $this->cache;
         $action->html = $this->html;
         $action->request = $this->request;
         $action->session = $this->session;
         $action->cookie = $this->cookie;
         return $action->run();
      }
   }

   public function loadController($ctrlerClass)
   {
      if (strpos($ctrlerClass, '\\') === FALSE)
      {
         $ctrlerClass = substr($this->class, 0, strrpos($this->class, '\\') + 1) . $ctrlerClass;
      }

      $ctrler = new $ctrlerClass($this->request->language, $this->path['language']);
      $ctrler->path = $this->path;
      $ctrler->logger = $this->logger;
      $ctrler->cache = $this->cache;
      $ctrler->html = $this->html;
      $ctrler->request = $this->request;
      $ctrler->session = $this->session;
      $ctrler->cookie = $this->cookie;

      return $ctrler;
   }

   public function error($msg)
   {
      Cache::$status = FALSE;
      $this->html->var['content'] = $this->l('Error') . ' : ' . $msg;
      $this->request->pageExit((string) $this->html);
   }

   public function l($key)
   {
      return \array_key_exists($key, self::$l[$this->class]) ? self::$l[$this->class][$key] : '[' . $key . ']';
   }

}

//__END_OF_FILE__
