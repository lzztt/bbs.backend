<?php

namespace site\controller\user;

use site\controller\User;
use site\dbobject\User as UserObject;
use site\dbobject\PrivMsg;
use lzx\html\HTMLElement;
use lzx\html\Form;
use lzx\html\TextArea;
use lzx\html\Template;
use lzx\core\Mailer;

class PasswordCtrler extends User
{

   /**
    * default protected methods
    */
   protected function init()
   {
      parent::_init();
      // don't cache user page at page level
      $this->cache->setStatus( FALSE );
   }

   public function run()
   {
      $action = $this->request->uid == self::GUEST_UID ? 'login' : 'display';
      $this->$action();
   }

   /**
    * public methods
    */
   public function register()
   {
      if ( $this->request->uid != self::GUEST_UID )
      {
         $this->error( '错误：用户已经登录，不能注册新用户' );
      }

      if ( empty( $this->request->post ) )
      {
         $this->html->var[ 'content' ] = new Template( 'user_register', ['captcha' => '/captcha/' . \mt_rand() ] );
      }
      else
      {
         if ( \strtolower( $this->session->captcha ) != \strtolower( $this->request->post[ 'captcha' ] ) )
         {
            $this->error( '错误：图形验证码错误' );
         }
         unset( $this->session->captcha );

         // check username and email first
         if ( empty( $this->request->post[ 'username' ] ) )
         {
            $this->error( '请填写用户名' );
         }

         if ( !\filter_var( $this->request->post[ 'email' ], \FILTER_VALIDATE_EMAIL ) )
         {
            $this->error( '不合法的电子邮箱 : ' . $this->request->post[ 'email' ] );
         }

         if ( isset( $this->request->post[ 'submit' ] ) || $this->_isBot( $this->request->post[ 'email' ] ) )
         {
            $this->logger->info( 'STOP SPAMBOT : ' . $this->request->post[ 'email' ] );
            $this->error( '系统检测到可能存在的注册机器人。所以不能提交您的注册申请，如果您认为这是一个错误的判断，请与网站管理员联系。' );
         }

         $user = new UserObject();
         $user->username = $this->request->post[ 'username' ];
         $user->email = $this->request->post[ 'email' ];
         $user->createTime = $this->request->timestamp;
         $user->lastAccessIP = (int) \ip2long( $this->request->ip );
         try
         {
            $user->add();
         }
         catch ( \PDOException $e )
         {
            $this->logger->error( $e->getMessage(), $e->getTrace() );
            $this->error( $e->errorInfo[ 2 ] );
         }
         // create user action and send out email
         $mailer = new Mailer();
         $mailer->to = $user->email;
         $mailer->subject = $user->username . ' 的HoustonBBS账户激活和设置密码链接';
         $contents = [
            'username' => $user->username,
            'uri' => $this->_createUser( $user->id, '/user/activate' ),
            'sitename' => 'HoustonBBS'
         ];
         $mailer->body = new Template( 'mail/newuser', $contents );

         if ( $mailer->send() === FALSE )
         {
            $this->error( 'sending new user activation email error: ' . $user->email );
         }
         $this->html->var[ 'content' ] = '感谢注册！账户激活email已经成功发送到您的注册邮箱 ' . $user->email . ' ，请检查email并且按其中提示激活账户。<br />如果您的收件箱内没有帐号激活的电子邮件，请检查电子邮件的垃圾箱，或者与网站管理员联系。';
      }
   }

   public function activate()
   {
      // forward to password controller
      $this->_forward( '/password/reset' );
   }

   public function password()
   {
      // forward to password controller
      $this->_forward( '/password' );
   }

   public function username()
   {
      if ( $this->request->uid != self::GUEST_UID )
      {
         $this->request->redirect( '/user' );
      }

      if ( empty( $this->request->post ) )
      {
         $this->html->var[ 'content' ] = new Template( 'user_forgetusername' );
      }
      else
      {
         if ( !\filter_var( $this->request->post[ 'email' ], \FILTER_VALIDATE_EMAIL ) )
         {
            $this->error( 'invalid email address : ' . $this->request->post[ 'email' ] );
         }

         $user = new UserObject();
         $user->email = $this->request->post[ 'email' ];
         $user->load( 'username' );

         if ( $user->exists() )
         {
            $response = '您的注册用户名是: ' . $user->username;
         }
         else
         {
            $response = '未发现使用该注册邮箱的账户，请检查邮箱是否正确: ' . $user->email;
         }
         $this->html->var[ 'content' ] = $response;
      }
   }

   public function login()
   {
      if ( $this->request->uid != self::GUEST_UID )
      {
         $this->error( '错误：您已经成功登录，不能重复登录。' );
      }

      if ( empty( $this->request->post ) )
      {
         // display login form
         $this->html->var[ 'content' ] = new Template( 'user_login' );
      }
      else
      {
         if ( isset( $this->request->post[ 'username' ] ) && isset( $this->request->post[ 'password' ] ) )
         {
            // todo: login times control
            $user = new UserObject();
            if ( $user->login( $this->request->post[ 'username' ], $this->request->post[ 'password' ] ) )
            {
               $this->_setUser( $user->id );
               $uri = $this->_getLoginRedirect();
               $this->request->redirect( $uri ? $uri : '/'  );
            }
            else
            {
               $this->logger->info( 'Login Fail: ' . $user->username . ' @ ' . $this->request->ip );
               if ( isset( $user->id ) )
               {
                  if ( $user->status == 1 )
                  {
                     $this->error( '错误：错误的密码。' );
                  }
                  else
                  {
                     $this->error( '错误：该帐号已被封禁，如有问题请联络网站管理员。' );
                  }
               }
               else
               {
                  $this->error( '错误：错误的用户名。' );
               }
            }
         }
         else
         {
            $this->error( '错误：请填写用户名和密码。' );
         }
      }
   }

   public function logout()
   {
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->error( '错误：您尚未成功登录，不能登出。' );
      }

      // logout to switch back to super user
      if ( isset( $this->session->suid ) )
      {
         $this->_switchUser();
         return;
      }

      //session_destroy();
      $this->session->clear(); // keep session record but clear the whole $_SESSION variable
      $this->cookie->uid = 0;
      $this->cookie->urole = Template::UROLE_GUEST;
      unset( $this->cookie->pmCount );
      $this->request->redirect( '/' );
   }

   // switch to user or back to super user
   public function _switchUser()
   {
      // switch to user from super user
      if ( $this->session->uid == self::ADMIN_UID )
      {
         if ( \filter_var( $this->args[ 0 ], \FILTER_VALIDATE_INT, ['options' => ['min_range' => 2 ] ] ) )
         {
            $user = new UserObject( $this->args[ 0 ], 'username' );
            if ( $user->exists() )
            {
               $this->logger->info( 'switching from user ' . $this->session->uid . ' to user ' . $user->id . '[' . $user->username . ']' );
               $this->session->suid = $this->session->uid;
               $this->_setUser( $user->id );
               $this->html->var[ 'content' ] = 'switched to user [' . $user->username . '], use "logout" to switch back to super user';
            }
            else
            {
               $this->error( '错误：user does not exist' );
            }
         }
         else
         {
            $this->error( '错误：invalid user id' );
         }
      }
      // switch back to super user
      elseif ( isset( $this->session->suid ) )
      {
         $suid = $this->session->suid;
         unset( $this->session->suid );
         if ( $suid == self::ADMIN_UID )
         {
            $this->logger->info( 'switching back from user ' . $this->request->uid . ' to user ' . $suid );
            $this->_setUser( $suid );
            $this->html->var[ 'content' ] = 'not logged out, just switched back to super user';
         }
      }
      // hide from normal user
      else
      {
         $this->request->pageNotFound();
      }
   }

   public function delete()
   {
      $uid = (int) $this->args[ 0 ];
      if ( $this->request->uid == self::ADMIN_UID && $uid > 1 )  // only admin can delete user, can not delete admin
      {
         $user = new UserObject();
         $user->id = $uid;
         $user->delete();
         foreach ( $user->getAllNodeIDs() as $nid )
         {
            $this->cache->delete( '/node/' . $nid );
         }
         $this->html->var[ 'content' ] = '用户ID: ' . $uid . '已经从系统中删除。';
      }
      else
      {
         $this->request->pageForbidden();
      }
   }

//logged in user
   public function edit()
   {
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->_displayLogin( $this->request->uri );
      }

      $uid = empty( $this->args ) ? $this->request->uid : (int) $this->args[ 0 ];

      if ( $this->request->uid != $uid && $this->request->uid != self::ADMIN_UID )
      {
         $this->request->pageForbidden();
      }

      $u = new UserObject();

      if ( empty( $this->request->post ) )
      {
         $u->id = $uid;
         $u->load();

         if ( $u->exists() )
         {
            if ( $user->birthday )
            {
               $birthday = \sprintf( '%08u', $user->birthday );
               $byear = \substr( $birthday, 0, 4 );
               if ( $byear == '0000' )
               {
                  $byear = NULL;
               }
               $bmonth = \substr( $birthday, 4, 2 );
               $bday = \substr( $birthday, 6, 2 );
            }

            $currentURI = '/user/edit/' . $uid;
            $userLinks = $this->_getUserLinks( $uid, $currentURI );
            $info = [
               'action' => $currentURI,
               'userLinks' => $userLinks,
               'username' => $u->username,
               'avatar' => $u->avatar ? $user->avatar : '/data/avatars/avatar0' . mt_rand( 1, 5 ) . '.jpg',
               'qq' => $u->qq,
               'wechat' => $u->wechat,
               'website' => $u->website,
               'firstname' => $u->firstname,
               'lastname' => $u->lastname,
               'sex' => $u->sex,
               'byear' => $byear,
               'bmonth' => $bmonth,
               'bday' => $bday,
               'occupation' => $u->occupation,
               'interests' => $u->interests,
               'aboutme' => $u->favoriteQuotation
            ];

            $this->html->var[ 'content' ] = new Template( 'user_edit', $info );
         }
         else
         {
            $this->error( '错误：用户不存在' );
         }
      }
      else
      {
         $u->id = $uid;

         $file = $this->request->files[ 'avatar' ][ 0 ];
         if ( $file[ 'error' ] == 0 && $file[ 'size' ] > 0 )
         {
            $fileInfo = getimagesize( $file[ 'tmp_name' ] );
            if ( $fileInfo === FALSE || $fileInfo[ 0 ] > 120 || $fileInfo[ 1 ] > 120 )
            {
               $this->error( '修改头像错误：上传头像图片尺寸太大。最大允许尺寸为 120 x 120 像素。' );
               return;
            }
            else
            {
               $avatar = '/data/avatars/' . $uid . '-' . \mt_rand( 0, 999 ) . \image_type_to_extension( $fileInfo[ 2 ] );
               \move_uploaded_file( $file[ 'tmp_name' ], $this->config->path[ 'file' ] . $avatar );
               $u->avatar = $avatar;
            }
         }

         $fields = [
            'wechat' => 'wechat',
            'qq' => 'qq',
            'website' => 'website',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'occupation' => 'occupation',
            'interests' => 'interests',
            'favoriteQuotation' => 'aboutme',
            'relationship' => 'relationship',
            'signature' => 'signature'
         ];

         foreach ( $fields as $k => $f )
         {
            $u->$k = \strlen( $this->request->post[ $f ] ) ? $this->request->post[ $f ] : NULL;
         }

         $u->sex = \is_numeric( $this->request->post[ 'sex' ] ) ? (int) $this->request->post[ 'sex' ] : NULL;

         $u->birthday = (int) ($this->request->post[ 'byear' ] . $this->request->post[ 'bmonth' ] . $this->request->post[ 'bday' ]);

         $u->update();

         $this->html->var[ 'content' ] = '您的最新资料已被保存。';

         $this->cache->delete( 'authorPanel' . $u->id );
      }
   }

   public function display()
   {
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->_displayLogin( $this->request->uri );
      }

      $uid = empty( $this->args ) ? $this->request->uid : (int) $this->args[ 0 ];
      // user are not allowed to view ADMIN's info
      if ( $uid == self::ADMIN_UID && $this->request->uid != self::ADMIN_UID )
      {
         $this->request->pageForbidden();
      }

      $user = new UserObject( $uid );
      if ( !$user->exists() )
      {
         $this->error( '错误：用户不存在' );
      }

      $sex = \is_null( $user->sex ) ? '未知' : ( $user->sex == 1 ? '男' : '女');
      if ( $user->birthday )
      {
         $birthday = \substr( \sprintf( '%08u', $user->birthday ), 4, 4 );
         $birthday = \substr( $birthday, 0, 2 ) . '/' . \substr( $birthday, 2, 2 );
      }
      else
      {
         $birthday = '未知';
      }

      $content = [
         'uid' => $uid,
         'username' => $user->username,
         'avator' => $user->avatar ? $user->avatar : '/data/avatars/avatar0' . \mt_rand( 1, 5 ) . '.jpg',
         'userLinks' => $this->_getUserLinks( $uid, '/user/display/' . $uid ),
         'pm' => $uid != $this->request->uid ? '/user/pm/' . $uid : '',
         'info' => [
            '微信' => $user->wechat,
            'QQ' => $user->qq,
            '个人网站' => $user->website,
            '性别' => $sex,
            '生日' => $birthday,
            '职业' => $user->occupation,
            '兴趣爱好' => $user->interests,
            '自我介绍' => $user->favoriteQuotation,
            '注册时间' => \date( 'm/d/Y H:i:s T', $user->createTime ),
            '上次登录时间' => \date( 'm/d/Y H:i:s T', $user->lastAccessTime ),
            '上次登录地点' => $this->request->getLocationFromIP( $user->lastAccessIP )
         ],
         'topics' => $user->getRecentNodes( 10 ),
         'comments' => $user->getRecentComments( 10 )
      ];

      $this->html->var[ 'content' ] = new Template( 'user_display', $content );
   }

   public function pm()
   {
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->_displayLogin( $this->request->uri );
      }

      $uid = empty( $this->args ) ? $this->request->uid : (int) $this->args[ 0 ];
      $user = new UserObject( $uid, NULL );

      if ( $user->id == $this->request->uid )
      {
         // show pm mailbox
         $mailbox = \sizeof( $this->args ) > 1 ? $this->args[ 1 ] : 'inbox';

         if ( !\in_array( $mailbox, ['inbox', 'sent' ] ) )
         {
            $this->error( '短信文件夹[' . $mailbox . ']不存在。' );
         }

         $pmCount = $user->getPrivMsgsCount( $mailbox );
         if ( $pmCount == 0 )
         {
            $this->error( $mailbox == 'sent' ? '您的发件箱里还没有短信。' : '您的收件箱里还没有短信。'  );
         }

         $currentURI = '/user/pm/' . $uid;
         $userLinks = $this->_getUserLinks( $uid, $currentURI );

         $activeLink = '/user/pm/' . $uid . '/' . $mailbox;
         $mailBoxLinks = $this->_getMailBoxLinks( $uid, $activeLink );

         $pager = $this->getPager( $pmCount, 25, $activeLink );
         $msgs = $user->getPrivMsgs( $mailbox, 25, ($pageNo - 1) * 25 );

         $thead = ['cells' => ['短信', '联系人', '时间' ] ];
         $tbody = [ ];
         foreach ( $msgs as $i => $m )
         {
            $msgs[ $i ][ 'body' ] = $this->html->truncate( $m[ 'body' ] );
            $msgs[ $i ][ 'time' ] = \date( 'm/d/Y H:i', $m[ 'time' ] );
         }

         $content = [
            'uid' => $user->id,
            'userLinks' => $userLinks,
            'mailBoxLinks' => $mailBoxLinks,
            'pager' => $pager,
            'msgs' => $msgs,
         ];

         $this->html->var[ 'content' ] = new Template( 'pm_list', $content );
      }
      else
      {
// show send pm to user page
         $user->load( 'username' );

         if ( empty( $this->request->post ) )
         {
// display pm edit form
            $content = [
               'breadcrumb' => $breadcrumb,
               'toUID' => $uid,
               'toName' => $user->username,
               'form_handler' => '/user/' . $uid . '/pm',
            ];
            $form = new Form( [
               'action' => '/user/' . $user->id . '/pm',
               'id' => 'user-pm-send'
               ] );
            $receipt = new HTMLElement( 'div', ['收信人: ', $this->html->link( $user->username, '/user/' . $user->id ) ] );
            $message = new TextArea( 'body', '短信正文', '最少5个字母或3个汉字', TRUE );

            $form->setData( [$receipt, $message->toHTMLElement() ] );
            $form->setButton( ['submit' => '发送短信' ] );
            $this->html->var[ 'content' ] = $form;
         }
         else
         {
// save pm to database
            if ( \strlen( $this->request->post[ 'body' ] ) < 5 )
            {
               $this->html->var[ 'content' ] = '错误：短信正文需最少5个字母或3个汉字。';
               return;
            }

            $pm = new PrivMsg();
            $pm->fromUID = $this->request->uid;
            $pm->toUID = $user->id;
            $pm->body = $this->request->post[ 'body' ];
            $pm->time = $this->request->timestamp;
            $pm->add();
            $pm->msgID = $pm->id;
            $pm->update( 'msgID' );

            $user->load( 'username,email' );
            $mailer = new Mailer();
            $mailer->to = $user->email;
            $mailer->subject = $user->username . ' 您有一封新的站内短信';
            $mailer->body = $user->username . ' 您有一封新的站内短信' . "\n" . '请登录后点击下面链接阅读' . "\n" . 'http://www.houstonbbs.com/pm/' . $pm->id;
            if ( !$mailer->send() )
            {
               $this->logger->error( 'PM EMAIL REMINDER SENDING ERROR: ' . $pm->id );
            }

            $this->html->var[ 'content' ] = '您的短信已经发送给用户 <i>' . $user->username . '</i>';
         }
      }
   }

   /**
    * protected methods
    */
   protected function _displayLogin( $redirect = NULL )
   {
      $this->_setLoginRedirect( $redirect ? $redirect : '/'  );
      $this->login();
      $this->request->pageExit( $this->html );
   }

   /**
    * 
    * private methods
    * 
    */
   private function _isBot( $m )
   {
      $try1 = unserialize( $this->request->curlGetData( 'http://www.stopforumspam.com/api?f=serial&email=' . $m ) );
      if ( $try1[ 'email' ][ 'appears' ] == 1 )
      {
         return TRUE;
      }
      $try2 = $this->request->curlGetData( 'http://botscout.com/test/?mail=' . $m );
      if ( $try2[ 0 ] == 'Y' )
      {
         return TRUE;
      }
      return FALSE;
   }

   private function _setUser( $uid )
   {
      $this->session->uid = $uid;
      $this->cookie->uid = $uid;
      $this->cookie->urole = $uid == self::GUEST_UID ? Template::UROLE_GUEST : ($uid == self::ADMIN_UID ? Template::UROLE_ADM : Template::UROLE_USER);
   }

   private function _recentTopics( $uid )
   {
      $user = new UserObject( $uid, NULL );

      if ( $uid == 1 && $this->request->uid != 1 )
      {
         $this->request->pageForbidden();
      }

      $posts = $user->getRecentNodes( 10 );

      $caption = '最近发表的论坛话题';
      $thead = ['cells' => ['论坛话题', '发表时间' ] ];
      $tbody = [ ];
      foreach ( $posts as $n )
      {
         $tbody[] = ['cells' => [$this->html->link( $this->html->truncate( $n[ 'title' ] ), '/node/' . $n[ 'nid' ] ), \date( 'm/d/Y H:i', $n[ 'create_time' ] ) ] ];
      }

      $recent_topics = $this->html->table( ['caption' => $caption, 'thead' => $thead, 'tbody' => $tbody ] );

      $posts = $user->getRecentComments( 10 );

      $caption = '最近回复的论坛话题';
      $thead = ['cells' => ['论坛话题', '回复时间' ] ];
      $tbody = [ ];
      foreach ( $posts as $n )
      {
         $tbody[] = ['cells' => [$this->html->link( $this->html->truncate( $n[ 'title' ] ), '/node/' . $n[ 'nid' ] ), \date( 'm/d/Y H:i', $n[ 'create_time' ] ) ] ];
      }

      $recent_comments = $this->html->table( ['caption' => $caption, 'thead' => $thead, 'tbody' => $tbody ] );

      return new HTMLElement( 'div', [$recent_topics, $recent_comments ], ['class' => 'user_recent_topics' ] );
   }

   private function _getUserLinks( $uid, $activeLink )
   {
      if ( $this->request->uid == $uid || $this->request->uid == self::ADMIN_UID )
      {
         return $this->html->linkList( [
               '/user/display/' . $uid => '用户首页',
               '/user/pm/' . $uid => '站内短信',
               '/user/edit/' . $uid => '编辑个人资料',
               '/password/change/' . $uid => '更改密码'
               ], $activeLink
         );
      }
   }

   private function _getMailBoxLinks( $uid, $activeLink )
   {
      if ( $this->request->uid == $uid || $this->request->uid == self::ADMIN_UID )
      {
         return $this->html->linkList( [
               '/user/pm/' . $uid . '/inbox' => '收件箱',
               '/user/pm/' . $uid . '/sent' => '发件箱'
               ], $activeLink
         );
      }
   }

}

//__END_OF_FILE__
