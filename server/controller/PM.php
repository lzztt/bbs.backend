<?php

namespace site\controller;

use site\Controller;
use site\dbobject\PrivMsg;
use lzx\html\HTMLElement;
use lzx\html\Form;
use lzx\html\Input;
use lzx\html\Hidden;
use lzx\html\TextArea;
use lzx\html\Template;

class PM extends Controller
{

    const PMS_PER_PAGE = 25;

    public function run()
    {
        parent::run();
        $this->cache->setStatus( FALSE );

        if ( $this->request->uid == 0 )
        {
            $this->cookie->loginReferer = $this->request->uri;
            $this->request->redirect( '/user' );
        }
        // logged in user
        else
        {
            $msgID = (int) ($this->request->args[1]);
            if ( $msgID > 0 )
            {
                $action = sizeof( $this->request->args ) > 2 ? $this->request->args[2] : 'display';
                $this->runAction( $action );
            }
        }
    }

    public function displayAction()
    {
        $msgID = \intval( $this->request->args[1] );

        $pm = new PrivMsg();
        $msgs = $pm->getPMConversation( $msgID, $this->request->uid );
        if ( \sizeof( $msgs ) == 0 )
        {
            $this->error( '错误：该条短信不存在。' );
            return;
        }

        $replyTo = $pm->getReplyTo( $msgID, $this->request->uid );

        $list = [];
        foreach ( $msgs as $m )
        {
            $avatar = new HTMLElement(
                'div', $this->html->link(
                    new HTMLElement( 'img', NULL, $attr = [
                    'alt' => $m['username'] . ' 的头像',
                    'src' => $m['avatar'] ? $m['avatar'] : '/data/avatars/avatar0' . \mt_rand( 1, 5 ) . '.jpg',
                    ] ), '/user/' . $m['uid'] ), ['class' => 'pm_avatar'] );

            $info = new HTMLElement(
                'div', [$this->html->link( $m['username'], '/user/' . $m['uid'] ), '<br />', \date( 'm/d/Y', $m['time'] ), '<br />', \date( 'H:i', $m['time'] )], ['class' => 'pm_info'] );

            $body = new HTMLElement(
                'div', \nl2br( $m['body'] ) . (new HTMLElement(
                'div', $this->html->link( ($m['id'] == $msgID ? '删除话题' : '删除' ), '/pm/' . $msgID . '/delete/' . $m['id'], ['class' => 'button'] ), ['class' => 'ed_actions'] )), ['class' => 'pm_body'] );
            $list[] = $avatar . $info . $body;
        }

        $messages = $this->html->ulist( $list, ['class' => 'pm_thread'] );

        $reply_form = new Form( array(
            'action' => '/pm/' . $msgID . '/reply',
            'id' => 'pm_reply'
            ) );
        $receipt = new Input( 'to', '收信人' );
        $receipt->attributes = ['readonly' => 'readonly'];
        $receipt->setValue( $replyTo['username'] );
        $message = new TextArea( 'body', '回复内容', '最少5个字母或3个汉字', TRUE );
        $toUID = new Hidden( 'toUID', $replyTo['uid'] );
        $fromUID = new Hidden( 'fromUID', $this->request->uid );

        $reply_form->setData( array(
            $receipt->toHTMLElement(),
            $message->toHTMLElement(),
            $fromUID->toHTMLElement(),
            $toUID->toHTMLElement()
        ) );
        $reply_form->setButton( array('submit' => '发送') );

        $this->html->var['content'] = $link_tabs . $pager . $messages . $reply_form;
    }

    public function replyAction()
    {
        $msgID = \intval( $this->request->args[1] );

        if ( $this->request->uid != $this->request->post['fromUID'] )
        {
            $this->error( '错误，用户没有权限回复此条短信' );
        }

        if ( \strlen( $this->request->post['body'] ) < 5 )
        {
            $this->error( '错误：短信正文字数太少。' );
        }

        $pm = new PrivMsg();
        $pm->msgID = $msgID;
        $pm->fromUID = $this->request->uid;
        $pm->toUID = $this->request->post['toUID'];
        $pm->body = $this->request->post['body'];
        $pm->time = $this->request->timestamp;
        $pm->add();

        $this->request->redirect( '/pm/' . $msgID );
    }

    public function deleteAction()
    {
        $msgID = \intval( $this->request->args[1] );
        $messageID = \intval( $this->request->args[3] );

        $pm = new PrivMsg();
        $pm->id = $messageID;
        try
        {
            $pm->deleteByUser( $this->request->uid );
        }
        catch ( \Exception $e )
        {
            $this->error( 'failed to delete message ' . $messageID . ' as user ' . $this->request->uid );
        }

        $redirect_uri = $msgID == $messageID ? '/user/pm' : '/pm/' . $msgID;
        $this->request->redirect( $redirect_uri );
    }

}

//__END_OF_FILE__
