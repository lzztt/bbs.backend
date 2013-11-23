<?php

namespace site\controller;

use site\Controller;
use site\dataobject\User as UserObject;
use site\dataobject\PrivMsg;
use lzx\html\HTMLElement;
use lzx\html\Form;
use lzx\html\Input;
use lzx\html\Select;
use lzx\html\TextArea;
use lzx\html\InputGroup;
use lzx\html\Template;
use lzx\core\Mailer;

class User extends Controller
{

   const ROOT_UID = 1;

   public function run()
   {
      parent::run();

      $args = $this->request->args;
// Anonymous user
      if ( $this->request->uid == 0 )
      {
         if ( \sizeof( $args ) == 1 ) // uri = /user
         {
            $action = 'login';
         }
         else
         {
            $action = $args[1];
            // uri = /user/<uid> ...
            if ( \filter_var( $action, \FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) ) )
            {
               // will need to set this cookie here!
               $this->cookie->loginReferer = $this->request->uri;
               $this->request->redirect( '/user/login' );
            }
         }
      }
// logged in user
      else
      {
         $this->cache->setStatus( FALSE ); // we don't cache at page level, different users have different PM page
         // no uid provided, uri = /user/<action>
         if ( !\filter_var( $args[1], \FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) ) )
         {
            $args = \array_merge( array( $args[0], $this->request->uid ), \array_slice( $args, 1 ) );
            $this->request->args = $args;
         }
         $action = \sizeof( $args ) > 2 ? $args[2] : 'display';
      }

      $this->runAction( $action );
   }

   public function registerAction()
   {
      if ( $this->request->uid > 0 )
      {
         $this->request->redirect( '/' );
      }

      $session = $this->session;

      if ( empty( $this->request->post ) )
      {
         $formttl = 'ttl_' . $this->request->hashURI();
         $session->$formttl = $this->request->timestamp + 7200;
         $link_tabs = $this->_link_tabs( '/user/register' );
         $form = new Form( array(
            'action' => '/user/register'
               ) );

         $fieldset = new HTMLElement( 'fieldset', new HTMLElement( 'legend', '帐户信息' ) );
         $username = new Input( 'username', '用户名', '允许空格，不允许"."、“-”、“_”以外的其他符号', TRUE );
         $email = new Input( 'email', '电子邮箱', '一个有效的电子邮件地址。帐号激活后的初始密码和所有本站发出的信件都将寄至此地址。电子邮件地址将不会被公开，仅当您想要接收新密码或通知时才会使用', TRUE );
         $email->type = 'email';
         $fieldset->addElements( $username->toHTMLElement(), $email->toHTMLElement() );
         $form->addElements( $fieldset );

         $fieldset = new HTMLElement( 'fieldset', new HTMLElement( 'legend', '联系方式' ) );
         $msn = new Input( 'msn', 'MSN' );
         $qq = new Input( 'qq', 'QQ' );
         $website = new Input( 'website', '个人网站' );
         $fieldset->addElements( $msn->toHTMLElement(), $qq->toHTMLElement(), $website->toHTMLElement() );
         $form->addElements( $fieldset );

         $fieldset = new HTMLElement( 'fieldset', new HTMLElement( 'legend', '个人信息' ) );
         $name = new InputGroup( '姓名', '不会公开显示' );
         $firstName = new Input( 'firstName', '名' );
         $lastName = new Input( 'lastName', '姓' );
         $name->addFormElemements( $firstName->inline(), $lastName->inline() );

         $sex = new Select( 'sex', '性别' );
         $sex->options = array(
            'null' => '未选择',
            '0' => '女',
            '1' => '男',
         );

         $birthday = new InputGroup( '生日', '用于计算年龄和星座，不会公开显示' );
         $bmonth = new Input( 'bmonth', '月(mm)' );
         $bday = new Input( 'bday', '日(dd)' );
         $byear = new Input( 'byear', '年(yyyy)' );
         $birthday->addFormElemements( $bmonth->inline(), $bday->inline(), $byear->inline() );

         $occupation = new Input( 'occupation', '职业' );
         $interests = new Input( 'interests', '兴趣爱好' );
         $aboutme = new TextArea( 'favoriteQuotation', '自我介绍' );
         $fieldset->addElements( $name->toHTMLElement(), $sex->toHTMLElement(), $birthday->toHTMLElement(), $occupation->toHTMLElement(), $interests->toHTMLElement(), $aboutme->toHTMLElement() );
         $form->addElements( $fieldset );

         $fieldset = new HTMLElement( 'fieldset', new HTMLElement( 'legend', '图形验证CAPTCHA' ) );
         $img = new HTMLElement( 'img', NULL, array( 'id' => 'captchaImage', 'title' => '图形验证', 'alt' => '图形验证未能正确显示，请刷新', 'src' => '/captcha' ) );
         $changeImage = new HTMLElement( 'a', '看不清，换一张', array( 'onclick' => 'document.getElementById(\'captchaImage\').setAttribute(\'src\',\'/captcha/\' + Math.random().toString().slice(2)); event.preventDefault();', 'href' => '#' ) );
         $captcha = new Input( 'captcha', '上面图片的内容是什么？', 'Enter the characters shown in the image', TRUE );
         $fieldset->addElements( $img, $changeImage, $captcha->toHTMLElement() );
         $form->addElements( $fieldset );
         $terms = new HTMLElement( 'fieldset', new HTMLElement( 'legend', '网站使用规范和免责声明' ) );
         $terms->addElements(
               new HTMLElement( 'a', '网站使用规范', array( 'href' => '/node/23200' ) ), new HTMLElement( 'br' ), new HTMLElement( 'a', '免责声明', array( 'href' => '/term' ) )
         );
         $form->addElements( $terms );
         $form->setButton( array( 'submit' => '同意使用规范和免责声明，并创建新帐号' ) );

         $this->html->var['content'] = $link_tabs . $form;
      }
      else
      {
         if ( \strtolower( $session->captcha ) != \strtolower( $this->request->post['captcha'] ) )
         {
            $this->error( '错误：图形验证码错误' );
         }
         unset( $this->request->post['captcha'] );
         unset( $session->captcha );

         // check username and email first
         if ( empty( $this->request->post['username'] ) )
         {
            $this->error( '请填写用户名' );
         }

         if ( !\filter_var( $this->request->post['email'], \FILTER_VALIDATE_EMAIL ) )
         {
            $this->error( '不合法的电子邮箱 : ' . $this->request->post['email'] );
         }

         if ( isset( $this->request->post['submit'] ) || $this->_isBot( $this->request->post['email'] ) )
         {
            $this->logger->info( 'STOP SPAMBOT : ' . $this->request->post['email'] );
            $this->error( '系统检测到可能存在的注册机器人。所以不能提交您的注册申请，如果您认为这是一个错误的判断，请与网站管理员联系。' );
         }

         $user = new UserObject();

         if ( $user->checkSpamEmail( $this->request->post['email'] ) === FALSE )
         {
            $this->error( '您填写的电子邮箱不能通过论坛注册' );
         }

         $user->username = $this->request->post['username'];
         if ( $user->getCount() > 0 )
         {
            $this->error( '您填写的用户名已被其他用户使用' );
         }
         $user = new UserObject();
         $user->email = $this->request->post['email'];
         if ( $user->getCount() > 0 )
         {
            $this->error( '您填写的电子邮箱已被其他用户使用' );
         }

         $this->request->post['birthday'] = (int) ($this->request->post['byear'] . $this->request->post['bmonth'] . $this->request->post['bday']);
         unset( $this->request->post['byear'] );
         unset( $this->request->post['bmonth'] );
         unset( $this->request->post['bday'] );

         foreach ( $this->request->post as $k => $v )
         {
            $user->$k = $v;
         }

         if ( !\is_numeric( $user->sex ) )
         {
            $user->sex = NULL;
         }
         $user->password = NULL; // will send generated password to email
         $user->status = NULL; // status NULL for new unactivated user
         $user->createTime = $this->request->timestamp;
         $user->lastAccessIPInt = (int) \ip2long( $this->request->ip );
         if ( isset( $user->birthday ) )
         {
            $_timestamp = strtotime( $user->birthday );
            $user->birthday = is_int( $_timestamp ) ? $_timestamp : null;
         }
         $user->add();
         $this->html->var['content'] = '感谢注册！您的帐号已被创建并等待管理员激活，一般会在一小时之内被激活，初始密码将在帐号被激活后邮寄至您的注册电子邮箱。<br />如果您在一小时之内没有收到帐号激活的电子邮件，请检查电子邮件的垃圾箱，或者与网站管理员联系。';
      }
   }

   private function _isBot( $m )
   {
      $try1 = unserialize( $this->request->curlGetData( 'http://www.stopforumspam.com/api?f=serial&email=' . $m ) );
      if ( $try1['email']['appears'] == 1 )
      {
         return TRUE;
      }
      $try2 = $this->request->curlGetData( 'http://botscout.com/test/?mail=' . $m );
      if ( $try2[0] == 'Y' )
      {
         return TRUE;
      }
      return FALSE;
   }

   public function usernameAction()
   {
      if ( $this->request->uid > 0 )
      {
         $this->request->redirect( '/' );
      }

      $formttl = 'ttl_' . $this->request->hashURI();
      $this->session->$formttl = $this->request->timestamp + 7200;
      $link_tabs = $this->_link_tabs( '/user/username' );
      $form = new Form( array(
         'action' => '/user/password',
         'id' => 'user-pass'
            ) );
      $email = new Input( 'email', '注册电子邮箱地址', '输入您注册时使用的电子邮箱地址', TRUE );
      $email->type = 'email';
      $form->setData( $email->toHTMLElement() );
      $form->setButton( array( 'submit' => '邮寄您的用户名' ) );

      $this->html->var['content'] = $link_tabs . $form;
   }

   public function passwordAction()
   {
      if ( $this->request->uid > 0 )
      {
         $this->request->redirect( '/' );
      }

      if ( empty( $this->request->post ) )
      {
         $formttl = 'ttl_' . $this->request->hashURI();
         $this->session->$formttl = $this->request->timestamp + 7200;
         $link_tabs = $this->_link_tabs( '/user/password' );
         $form = new Form( array(
            'action' => '/user/password',
            'id' => 'user-pass'
               ) );
         $username = new Input( 'username', '用户名', '输入您的用户名', TRUE );
         $email = new Input( 'email', '注册电子邮箱地址', '输入您注册时使用的电子邮箱地址', TRUE );
         $email->type = 'email';
         $form->setData( array( $username->toHTMLElement(), $email->toHTMLElement() ) );
         $form->setButton( array( 'submit' => '邮寄新的密码' ) );

         $this->html->var['content'] = $link_tabs . $form;
      }
      else
      {
         if ( empty( $this->request->post['username'] ) )
         {
            $this->error( 'empty username' );
         }
         if ( !\filter_var( $this->request->post['email'], \FILTER_VALIDATE_EMAIL ) )
         {
            $this->error( 'invalid email address : ' . $this->request->post['email'] );
         }

         $user = new UserObject();
         $user->username = $this->request->post['username'];
         $user->email = $this->request->post['email'];
         $user->load( 'uid,username,email,status' );
         if ( $user->status === NULL )
         {
            $this->error( '该帐号等待激活中，未激活前不能修改密码' );
         }
         if ( $user->status === 0 )
         {
            $this->error( '该帐号已被封禁，不能修改密码' );
         }
         if ( $user->exists() )
         {
            $password = $user->randomPW();
            $user->password = $user->hashPW( $password );
            $user->update( 'password' );

            $mailer = new Mailer();

            $mailer->to = $user->email;
            $mailer->subject = $user->username . ' 的新密码';
            $contents = array(
               'username' => $user->username,
               'password' => $password,
               'sitename' => 'HoustonBBS'
            );
            $mailer->body = new Template( 'mail/password_reset', $contents );
            if ( $mailer->send() )
            {
               $this->logger->info( 'user password reset: ' . $user->username . ' : ' . $password );
               $this->html->var['content'] = '用户 ' . $user->username . ' 的新密码已邮寄至 ' . $user->email . '。请查收邮件并且尝试用新密码登录。如有问题请联系网站管理员。';
            }
            else
            {
               $this->error( '新密码邮寄失败，请联系网站管理员重设密码。' );
            }
         }
      }
   }

// user login
   public function loginAction()
   {
      if ( $this->request->uid > 0 )
      {
         $this->request->redirect( '/' );
      }

      $this->cache->setStatus( FALSE );
      $ref = $this->request->referer;
      $guestActions = array( '/user', '/user/login', '/user/register', '/user/password', '/user/username' );
      //update page redirection
      if ( !\in_array( $ref, $guestActions ) )
      {
         $this->cookie->loginReferer = $ref;
      }

      if ( isset( $this->request->post['username'] ) && isset( $this->request->post['password'] ) )
      {
         // todo: login times control
         $user = new UserObject();
         if ( $user->login( $this->request->post['username'], $this->request->post['password'] ) )
         {
            $this->session->uid = $user->uid;
            $this->cookie->uid = $user->uid;
            $this->cookie->urole = ($user->uid == self::ROOT_UID) ? Template::UROLE_ADM : Template::UROLE_USER;
            $referer = '/';
            if ( $this->cookie->loginReferer )
            {
               $referer = $this->cookie->loginReferer;
               unset( $this->cookie->loginReferer );
            }
            $this->request->redirect( $referer );
         }
         elseif ( isset( $user->uid ) )
         {
            $this->logger->info( 'Login Fail: ' . $user->username . ' @ ' . $this->request->ip );
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
            $this->logger->info( 'Login Fail: ' . $user->username . ' @ ' . $this->request->ip );
            $this->error( '错误：错误的用户名。' );
         }
      }
      else
      {
// display login form
         $formttl = 'ttl_' . $this->request->hashURI();
         $this->session->$formttl = $this->request->timestamp + 7200;
         $link_tabs = $this->_link_tabs( '/user/login' );
         $form = new Form( array(
            'action' => '/user/login',
            'id' => 'user-login'
               ) );
         $username = new Input( 'username', '用户名', '输入您在 缤纷休斯顿 华人论坛 的用户名', TRUE );
         $password = new Input( 'password', '密码', '输入与您用户名相匹配的密码', TRUE );
         $password->type = 'password';
         $form->setData( array( $username->toHTMLElement(), $password->toHTMLElement() ) );
         $form->setButton( array( 'submit' => '登录' ) );

         $this->html->var['content'] = $link_tabs . $form;
      }
   }

   private function _link_tabs( $active_link )
   {
      if ( $this->request->uid == 0 )
      {
         $tabs = array(
            '/user/login' => '登录',
            '/user/register' => '创建新帐号',
            '/user/password' => '重设密码',
            '/user/username' => '忘记用户名',
         );
      }
      else
      {
         $uid = $this->request->args[1];
         $tabs = array(
            '/user/' . $uid . '/display' => '用户首页',
            '/user/' . $uid . '/edit' => '编辑个人资料',
            '/user/' . $uid . '/pm' => '站内短信',
         );
      }
      return $this->html->linkTabs( $tabs, $active_link );
   }

   // switch to user or back to super user
   public function switchAction()
   {
      // switch to user
      if ( $this->session->uid == self::ROOT_UID )
      {
         if ( \filter_var( $this->request->args[3], \FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 2 ) ) ) )
         {
            $user = new UserObject( $this->request->args[3], 'username' );
            if ( $user->exists() )
            {
               $this->logger->info( 'switching from user ' . $this->session->uid . ' to user ' . $user->uid . '[' . $user->username . ']' );
               $this->session->suid = $this->session->uid;
               $this->session->uid = $user->uid;
               $this->cookie->uid = $user->uid;
               $this->cookie->urole = ($user->uid == self::ROOT_UID) ? Template::UROLE_ADM : Template::UROLE_USER;
               $this->html->var['content'] = 'switched to user [' . $user->username . '], use "logout" to switch back to super user';
            }
            else
            {
               $this->error( 'user does not exist' );
            }
         }
         else
         {
            $this->error( 'invalid user id' );
         }
      }
      // switch back to super user
      elseif ( isset( $this->session->suid ) )
      {
         $suid = $this->session->suid;
         unset( $this->session->suid );
         if ( $suid == self::ROOT_UID )
         {
            $this->logger->info( 'switching back from user ' . $this->request->uid . ' to user ' . $suid );
            $this->session->uid = $suid;
            $this->cookie->uid = $suid;
            $this->cookie->urole = ($suid == self::ROOT_UID) ? Template::UROLE_ADM : Template::UROLE_USER;
            $this->html->var['content'] = 'not logged out, just switched back to super user';
         }
      }
      else
      {
         $this->request->pageNotFound();
      }
   }

// user logout
   public function logoutAction()
   {
      if ( $this->request->uid == 0 )
      {
         $this->cookie->urole = Template::UROLE_GUEST;
         $this->request->redirect( '/' );
      }

      // logout to switch back to super user
      if ( isset( $this->session->suid ) )
      {
         $this->runAction( 'switch' );
         return;
      }

      $this->cache->setStatus( FALSE );
      $uid = $this->request->args[1];
      if ( $this->request->uid == $uid )
      {
         //session_destroy();
         $this->session->clear(); // keep session record but clear the whole $_SESSION variable
         $this->cookie->uid = 0;
         $this->cookie->urole = Template::UROLE_GUEST;
         unset( $this->cookie->pmCount );
         $this->request->redirect( '/' );
      }
      else
      {
         $this->request->pageForbidden();
      }
   }

   public function deleteAction()
   {
      if ( $this->request->uid == 0 )
      {
         $this->request->redirect( '/user' );
      }

      $this->cache->setStatus( FALSE );
      $uid = intval( $this->request->args[1] );
      if ( ( $this->request->uid == 1 || $this->request->uid == $uid ) && $uid > 2 )  // can not delete uid = 1
      {
         $user = new UserObject();
         $user->uid = $uid;
         $user->delete();
         foreach ( $user->getAllNodeIDs() as $nid )
         {
            $this->cache->delete( '/node/' . $nid );
         }
         $this->html->var['content'] = '用户ID: ' . $uid . '已经从系统中删除。';
      }
      else
      {
         $this->request->pageForbidden();
      }
   }

//logged in user
   public function editAction()
   {
      if ( $this->request->uid == 0 )
      {
         $this->request->redirect( '/user' );
      }

      $uid = $this->request->args[1];
      $this->cache->setStatus( FALSE );

      $link_tabs = $this->_link_tabs( '/user/' . $uid . '/edit' );


      if ( $this->request->uid != $uid && $this->request->uid != 1 )
      {
         $this->request->pageForbidden();
      }

      if ( empty( $this->request->post ) )
      {
         $link_tabs = $this->_link_tabs( '/user/' . $uid . '/edit' );
         $form = new Form( array(
            'action' => $this->request->uri,
            'enctype' => 'multipart/form-data'
               ) );

         $user = new UserObject( $uid );

         $fieldset = new HTMLElement( 'fieldset', new HTMLElement( 'legend', '帐号设置' ) );
         $avatar = new Input( 'avatar', '用户头像', '您的虚拟头像。最大尺寸是 <em>120 x 120</em> 像素，最大大小为 <em>60</em> KB' );
         $avatar->type = 'file';
         $avatar_element = $avatar->toHTMLElement();
         $input = $avatar_element->getDataByIndex( 1 );
         $input->addElements( new HTMLElement( 'img', NULL, array( 'class' => 'avatar', 'src' => $user->avatar ? $user->avatar : '/data/avatars/avatar0' . mt_rand( 1, 5 ) . '.jpg' ) ) );
         $avatar_element->setDataByIndex( 1, $input );

         $signature = new TextArea( 'signature', '签名档', '您的签名将会公开显示在评论的末尾' );
         $signature->setValue( $user->signature );
         $fieldset->addElements( $avatar_element, $signature->toHTMLElement() );
         $form->addElements( $fieldset );

         $fieldset = new HTMLElement( 'fieldset', new HTMLElement( 'legend', '联系方式' ) );
         $msn = new Input( 'msn', 'MSN' );
         $msn->setValue( $user->msn );
         $qq = new Input( 'qq', 'QQ' );
         $qq->setValue( $user->qq );
         $website = new Input( 'website', '个人网站' );
         $website->setValue( $user->website );
         $fieldset->addElements( $msn->toHTMLElement(), $qq->toHTMLElement(), $website->toHTMLElement() );
         $form->addElements( $fieldset );

         $fieldset = new HTMLElement( 'fieldset', new HTMLElement( 'legend', '个人信息' ) );
         $name = new InputGroup( '姓名', '不会公开显示' );
         $firstName = new Input( 'firstName', '名' );
         $firstName->setValue( $user->firstName );
         $lastName = new Input( 'lastName', '姓' );
         $lastName->setValue( $user->lastName );
         $name->addFormElemements( $firstName->inline(), $lastName->inline() );

         $sex = new Select( 'sex', '性别' );
         $sex->options = array(
            'null' => '未选择',
            '0' => '女',
            '1' => '男',
         );
         $sex->setValue( \strval( $user->sex ) );

         if ( $user->birthday )
         {
            $birthday = sprintf( '%08u', $user->birthday );
            $value_byear = substr( $birthday, 0, 4 );
            if ( $value_byear == '0000' )
            {
               $value_byear = NULL;
            }
            $value_bmonth = substr( $birthday, 4, 2 );
            $value_bday = substr( $birthday, 6, 2 );
         }

         $birthday = new InputGroup( '生日', '用于计算年龄，出生年不会公开显示' );
         $bmonth = new Input( 'bmonth', '月(mm)' );
         $bmonth->setValue( $value_bmonth );
         $bday = new Input( 'bday', '日(dd)' );
         $bday->setValue( $value_bday );
         $byear = new Input( 'byear', '年(yyyy)' );
         $byear->setValue( $value_byear );
         $birthday->addFormElemements( $bmonth->inline(), $bday->inline(), $byear->inline() );

         $occupation = new Input( 'occupation', '职业' );
         $occupation->setValue( $user->occupation );
         $interests = new Input( 'interests', '兴趣爱好' );
         $interests->setValue( $user->interests );
         $aboutme = new TextArea( 'aboutme', '自我介绍' );
         $aboutme->setValue( $user->favoriteQuotation );
         $fieldset->addElements( $name->toHTMLElement(), $sex->toHTMLElement(), $birthday->toHTMLElement(), $occupation->toHTMLElement(), $interests->toHTMLElement(), $aboutme->toHTMLElement() );
         $form->addElements( $fieldset );

         $fieldset = new HTMLElement( 'fieldset', new HTMLElement( 'legend', '更改密码 (如果不想更改密码，此部分请留空)' ) );
         $password1 = new Input( 'password1', '新密码' );
         $password1->type = 'password';
         $password2 = new Input( 'password2', '确认新密码' );
         $password2->type = 'password';
         $fieldset->addElements( $password1->toHTMLElement(), $password2->toHTMLElement() );
         $form->addElements( $fieldset );

         $form->setButton( array( 'submit' => '保存' ) );

         $this->html->var['content'] = $link_tabs . $form;
         // display edit form
         //$this->html->var['content'] = new Template('user_edit', array('user' => new UserObject($uid)));
      }
      else
      {
         $user = new UserObject();
         $user->uid = $uid;

         $file = $_FILES['avatar'];
         if ( $file['error'] == 0 && $file['size'] > 0 )
         {
            $fileInfo = getimagesize( $file['tmp_name'] );
            if ( $fileInfo === FALSE || $fileInfo[0] > 120 || $fileInfo[1] > 120 )
            {
               $this->error( '修改头像错误：上传头像图片尺寸太大。最大允许尺寸为 120 x 120 像素。' );
               return;
            }
            else
            {
               $avatar = '/data/avatars/' . $uid . '-' . \mt_rand( 0, 999 ) . \image_type_to_extension( $fileInfo[2] );
               \move_uploaded_file( $file['tmp_name'], $this->path['file'] . $avatar );
               $user->avatar = $avatar;
            }
         }

         if ( $this->request->post['password2'] )
         {
            $password = $this->request->post['password2'];
            if ( $this->request->post['password1'] == $password )
            {
               $user->password = $user->hashPW( $password );
            }
            else
            {
               $this->error( '修改密码错误：两次输入的新密码不一致。 ' );
               return;
            }
         }

         $fields = array(
            'msn' => 'msn',
            'qq' => 'qq',
            'website' => 'website',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'occupation' => 'occupation',
            'interests' => 'interests',
            'favoriteQuotation' => 'aboutme',
            'relationship' => 'relationship',
            'signature' => 'signature'
         );

         foreach ( $fields as $k => $f )
         {
            $user->$k = \strlen( $this->request->post[$f] ) ? $this->request->post[$f] : NULL;
         }

         if ( !\is_numeric( $this->request->post['sex'] ) )
         {
            $user->sex = NULL;
         }
         else
         {
            $user->sex = $this->request->post['sex'];
         }

         $user->birthday = (int) ($this->request->post['byear'] . $this->request->post['bmonth'] . $this->request->post['bday']);

         $user->update();

         $this->html->var['content'] = '您的最新资料已被保存。';

         $this->cache->delete( 'authorPanel' . $user->uid );
         $this->cache->delete( '/user/' . $user->uid );
         $this->cache->delete( '/user/' . $user->uid . '/*' );
      }
   }

   public function displayAction()
   {
      if ( $this->request->uid == 0 )
      {
         $this->request->redirect( '/user' );
      }
// view: the default action
      $uid = $this->request->args[1];

      if ( $uid == $this->request->uid )
      {
         $link_tabs = $this->_link_tabs( '/user/' . $uid . '/display' );
      }
      else
      {
         $link_tabs = '';
      }

      $info = array( );
      $user = new UserObject( $uid );
      $info[] = array( 'dt' => '用户名', 'dd' => $user->username );
      $info[] = array( 'dt' => 'MSN', 'dd' => $user->msn );
      $info[] = array( 'dt' => 'QQ', 'dd' => $user->qq );
      $info[] = array( 'dt' => '个人网站', 'dd' => $user->website );
      $sex = \is_null( $user->sex ) ? '未知' : ( $user->sex == 1 ? '男' : '女');
      $info[] = array( 'dt' => '性别', 'dd' => $sex );
      if ( $user->birthday )
      {
         $birthday = \substr( \sprintf( '%08u', $user->birthday ), 4, 4 );
         $birthday = \substr( $birthday, 0, 2 ) . '/' . \substr( $birthday, 2, 2 );
      }
      else
      {
         $birthday = '未知';
      }
      $info[] = array( 'dt' => '生日', 'dd' => $birthday );
      $info[] = array( 'dt' => '职业', 'dd' => $user->occupation );
      $info[] = array( 'dt' => '兴趣爱好', 'dd' => $user->interests );
      $info[] = array( 'dt' => '自我介绍', 'dd' => $user->favoriteQuotation );

      $info[] = array( 'dt' => '注册时间', 'dd' => \date( 'm/d/Y H:i:s T', $user->createTime ) );
      $info[] = array( 'dt' => '上次登录时间', 'dd' => \date( 'm/d/Y H:i:s T', $user->lastAccessTime ) );

      $info[] = array( 'dt' => '上次登录地点', 'dd' => $this->request->getLocationFromIP( $user->lastAccessIPInt ) );

      $dlist = $this->html->dlist( $info );


      $pic = $user->avatar ? $user->avatar : '/data/avatars/avatar0' . mt_rand( 1, 5 ) . '.jpg';
      $avatar = new HTMLElement( 'div', NULL, array( 'class' => 'avatar_div' ) );
      $avatar->addElements( new HTMLElement( 'img', NULL, array( 'class' => 'avatar', 'src' => $pic, 'alt' => $user->username . '的头像' ) ) );
      if ( $uid != $this->request->uid )
      {
         $avatar->addElements( $this->html->link( '发送站内短信', '/user/' . $uid . '/pm', array( 'class' => 'button' ) ) );
      }
      $info = new HTMLElement( 'div', array( $avatar, $dlist ) );

      $this->html->var['content'] = $link_tabs . $info . $this->_recentTopics();
   }

   public function pmAction()
   {
      if ( $this->request->uid == 0 )
      {
         $this->request->redirect( '/user' );
      }
      $uid = $this->request->args[1];
      $user = new UserObject( $uid );
      $this->cache->setStatus( FALSE );

      if ( $user->uid == $this->request->uid )
      {
         $link_tabs = $this->_link_tabs( '/user/' . $user->uid . '/pm' );
         // show pm mailbox
         $mailbox = \sizeof( $this->request->args ) > 3 ? $this->request->args[3] : 'inbox';

         if ( !\in_array( $mailbox, array( 'inbox', 'sent' ) ) )
         {
            $this->error( '短信文件夹[' . $mailbox . ']不存在。' );
         }

         $pmCount = $user->getPrivMsgsCount( $mailbox );
         if ( $pmCount == 0 )
         {
            $this->error( $mailbox == 'sent' ? '您的发件箱里还没有短信。' : '您的收件箱里还没有短信。'  );
         }

         $activeLink = '/user/' . $user->uid . '/pm/' . $mailbox;
         $mailboxList = $this->html->linkTabs( array(
            '/user/' . $user->uid . '/pm/inbox' => '收件箱',
            '/user/' . $user->uid . '/pm/sent' => '发件箱'
               ), $activeLink
         );

         $pageNo = (int) $this->request->get['page'];
         $pageCount = ceil( $pmCount / 25 );
         if ( $pageCount > 0 )
         {
            list($pageNo, $pager) = $this->html->generatePager( $pageNo, $pageCount, $activeLink );
            $msgs = $user->getPrivMsgs( $mailbox, 25, ($pageNo - 1) * 25 );
         }

         $thead = array( 'cells' => array( '短信', '联系人', '时间' ) );
         $tbody = array( );
         foreach ( $msgs as $m )
         {
            $words = ($m['isNew'] == 1 ? '<span style="color:red;">new</span> ' : '') . $this->html->truncate( $m['body'] );
            $tbody[] = array( 'cells' => array(
                  $this->html->link( $words, '/pm/' . $m['topicMID'] ),
                  $m['fromName'] . ' -> ' . $m['toName'],
                  \date( 'm/d/Y H:i', $m['time'] )
               ) );
         }

         $messages = $this->html->table( array( 'thead' => $thead, 'tbody' => $tbody ) );

         $this->html->var['content'] = $link_tabs . $mailboxList . $pager . $messages . $pager;
      }
      else
      {
         // show send pm to user page
         $user->load( 'username' );

         if ( empty( $this->request->post ) )
         {
            // display pm edit form
            $content = array(
               'breadcrumb' => $breadcrumb,
               'toUID' => $uid,
               'toName' => $user->username,
               'form_handler' => '/user/' . $uid . '/pm',
            );
            $form = new Form( array(
               'action' => '/user/' . $user->uid . '/pm',
               'id' => 'user-pm-send'
                  ) );
            $receipt = new HTMLElement( 'div', array( '收信人: ', $this->html->link( $user->username, '/user/' . $user->uid ) ) );
            $message = new TextArea( 'body', '短信正文', '最少5个字母或3个汉字', TRUE );

            $form->setData( array( $receipt, $message->toHTMLElement() ) );
            $form->setButton( array( 'submit' => '发送短信' ) );
            $this->html->var['content'] = $form;
         }
         else
         {
            // save pm to database
            if ( \strlen( $this->request->post['body'] ) < 5 )
            {
               $this->html->var['content'] = '错误：短信正文需最少5个字母或3个汉字。';
               return;
            }

            $pm = new PrivMsg();
            $pm->fromUID = $this->request->uid;
            $pm->toUID = $user->uid;
            $pm->body = $this->request->post['body'];
            $pm->time = $this->request->timestamp;
            $pm->isNew = 1;
            $pm->isDeleted = 0;
            $pm->add();
            $pm->topicMID = $pm->mid;
            $pm->update( 'topicMID' );

            $this->html->var['content'] = '您的短信已经发送给用户 <i>' . $user->username . '</i>';
         }
      }
   }

   private function _recentTopics()
   {
      if ( $this->request->uid == 0 )
      {
         $this->request->redirect( '/user' );
      }
      $uid = $this->request->args[1];
      $user = new UserObject( $uid );
      $this->cache->setStatus( FALSE );

      $link_tabs = $this->_link_tabs( '/user/' . $uid . '/track' );

      if ( $uid == 1 && $this->request->uid != 1 )
      {
         $this->request->pageForbidden();
      }
      $posts = $user->getRecentNodes( 10 );

      $caption = '最近发表的论坛话题';
      $thead = array( 'cells' => array( '论坛话题', '发表时间' ) );
      $tbody = array( );
      foreach ( $posts as $n )
      {
         $tbody[] = array( 'cells' => array( $this->html->link( $this->html->truncate( $n['title'] ), '/node/' . $n['nid'] ), date( 'm/d/Y H:i', $n['createTime'] ) ) );
      }

      $recent_topics = $this->html->table( array( 'caption' => $caption, 'thead' => $thead, 'tbody' => $tbody ) );

      $posts = $user->getRecentComments( 10 );

      $caption = '最近回复的论坛话题';
      $thead = array( 'cells' => array( '论坛话题', '回复时间' ) );
      $tbody = array( );
      foreach ( $posts as $n )
      {
         $tbody[] = array( 'cells' => array( $this->html->link( $this->html->truncate( $n['title'] ), '/node/' . $n['nid'] ), date( 'm/d/Y H:i', $n['createTime'] ) ) );
      }

      $recent_comments = $this->html->table( array( 'caption' => $caption, 'thead' => $thead, 'tbody' => $tbody ) );

      return new HTMLElement( 'div', array( $recent_topics, $recent_comments ), array( 'class' => 'user_recent_topics' ) );
   }

}

//__END_OF_FILE__
