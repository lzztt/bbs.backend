<?php

require_once dirname(__DIR__) . '/apps/common.php';

define('TPL_PATH', ROOT . TPL_URL);

$flag = ROOT . '/mail.finished.log';

if (!file_exists($flag))
{
  $timer = microtime(TRUE);

  $func = 'do_mail';
  require_once ROOT . '/settings.php';
  require_once CLS_PATH . 'Log.cls.php';
  require_once CLS_PATH . 'MySQL.cls.php';
  require_once CLS_PATH . 'Theme.cls.php';

  $db = MySQL::getInstance();
  if ($func($db))
  {
    Log::info('SEND MAIL AT ' . DATETIME . ', USING ' . round((microtime(TRUE) - $timer), 3) . ' Second');
  }
  else
  {
    touch($flag);
    Log::info('MAIL QUEUE IS EMPTY AT ' . DATETIME);
  }
}


function do_mail($db)
{

  $users = $db->select('SELECT uid, username, email FROM mails WHERE status IS NULL LIMIT 20');

  if (sizeof($users) > 0)
  {
    require_once CLS_PATH . 'Mailer.cls.php';
    $mailer = new Mailer();
    $mailer->from = 'admin';
    $mailer->subject = 'HoustonBBS Lottery 缤纷节日抽奖';
    foreach ($users as $u)
    {
      $mailer->to = $u['email'];

      $contents = array(
          'username' => $u['username']
      );
      $mailer->body = Theme::render('mail/lottery', $contents);
      if ($mailer->send())
      {
        $db->query('UPDATE mails SET status = 1 WHERE uid = ' . $u['uid']);
      }
      else
      {
        $db->query('UPDATE mails SET status = 0 WHERE uid = ' . $u['uid']);
        Log::info('News Letter Email Sending Error: ' . $u['uid']);
      }
      sleep(2);
    }
    return TRUE;
  }

}

class Language
{

   private $_pool;
   private $_name;
   private $_lang;

   public function __construct($name, $lang=null)
   {
      $this->_name = empty($name) ? 'system' : $name;
      $this->_lang = in_array($lang, array('en', 'zh-hans', 'zh-hant')) ? $lang : LANG_DEFAULT;
   }

   public function s($key, $vars=array()) // get a language string
   {
      if (!isset($this->_pool))
      {
         include TPL_PATH . 'languages/' . $this->_lang . '/' . $this->_name . '.lang.php';
         $this->_pool = $l;
      }

      if (isset($this->_pool[$key]) && is_string($this->_pool[$key]))
      {
         extract($vars, EXTR_SKIP);  // Extract the variables to a local namespace
         $val = $this->_pool[$key];
      }
      else
      {
         $val = '[' . TPL . ':' . $this->_lang . ':' . $this->_name . ':' . $key . ']';
      }
      return $val;
   }

}
//__END_OF_FILE__