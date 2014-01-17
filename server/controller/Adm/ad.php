<?php

namespace site\controller\Adm;

use lzx\core\ControllerAction;
use lzx\html\Template;
use site\dbobject\AD as ADObject;
use site\dbobject\ADPayment;
use lzx\html\Form;
use lzx\html\Input;
use lzx\html\Select;
use lzx\html\TextArea;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of adm
 *
 * @author ikki
 */
class ad extends ControllerAction
{

   public function run()
   {
      $function = $this->request->args[2] ? $this->request->args[2] : 'show';

      if ( \method_exists( $this, $function ) )
      {
         return $this->$function();
      }
      else
      {
         throw new \Exception( $this->l( 'action_not_found' ) . ' : ' . $action );
      }
   }

   public function show()
   {
      $ad = new ADObject();
      $a_month_ago = $this->request->timestamp - 2592000;
      $contents = [
         'ads' => $ad->getAllAds( $a_month_ago ),
         'payments' => $ad->getAllAdPayments( $a_month_ago )
      ];
      return new Template( 'ads', $contents );
   }

   public function add()
   {
      $ad = new ADObject();
      // show add form
      if ( \sizeof( $this->request->post ) == 0 )
      {
         $form = new Form( [
            'action' => $this->request->uri,
            'id' => 'ad-add'
               ] );
         $name = new Input( 'name', '名称', '广告名称', TRUE );
         $email = new Input( 'email', 'Email', '联系Email', TRUE );
         $type = new Select( 'type_id', '类别', '广告类别', TRUE );

         foreach ( $ad->getAllAdTypes() as $at )
         {
            $type->options[$at['id']] = $at['name'];
         }
         $form->setData( [ $name->toHTMLElement(), $email->toHTMLElement(), $type->toHTMLElement() ] );
         $form->setButton( [ 'submit' => '添加广告' ] );

         return $form;
      }
      // add record
      else
      {
         $ad->name = $this->request->post['name'];
         $ad->email = $this->request->post['email'];
         $ad->typeID = $this->request->post['type_id'];
         $ad->expTime = $this->request->timestamp;
         $ad->add();
         return '广告添加成功: ' . $ad->name . ' : ' . $ad->email;
      }
   }

   public function payment()
   {
      $ad = new ADObject();
      // show add form
      if ( \sizeof( $this->request->post ) == 0 )
      {
         $form = new Form( [
            'action' => $this->request->uri,
            'id' => 'adpayment-add'
               ] );
         $ad_id = new Select( 'ad_id', '广告名称', '广告名称', TRUE );
         $amount = new Input( 'amount', '金额 ($)', '付款金额，单位为美元', TRUE );
         $time = new Input( 'time', '付款时间', '付款时间', TRUE );
         $time->setValue( \date( 'm/d/Y', $this->request->timestamp ) );
         $ad_time = new Input( 'ad_time', '广告时间 (月)', '广告有效时间，单位为月', TRUE );
         $ad_time->setValue( 3 );
         $comment = new TextArea( 'comment', '备注', '付款备注', TRUE );


         foreach ( $ad->getAllAds() as $a )
         {
            $ad_id->options[$a['id']] = $a['name'];
         }
         $form->setData( [ $ad_id->toHTMLElement(), $amount->toHTMLElement(), $time->toHTMLElement(), $ad_time->toHTMLElement(), $comment->toHTMLElement() ] );
         $form->setButton( [ 'submit' => '添加付款' ] );

         return $form;
      }
      // add record
      else
      {
         $ap = new ADPayment();
         $ap->adID = $this->request->post['ad_id'];
         $ap->amount = $this->request->post['amount'];
         $ap->time = \strtotime( $this->request->post['time'] );
         $ap->comment = $this->request->post['comment'];
         $ap->add();

         $ad->id = $ap->adID;
         $ad->load( 'name,expTime' );
         if ( $ad->expTime < $this->request->timestamp )
         {
            $exp_time = $this->request->post['time'];
         }
         else
         {
            $exp_time = \date( 'm/d/Y', $ad->expTime );
         }
         $ad->expTime = \strtotime( $exp_time . ' +' . $this->request->post['ad_time'] . ' months' );
         $ad->update( 'expTime' );

         return '付款添加成功: ' . $ad->name . ' : $' . $ap->amount . '<br>广告有效期更新至: ' . \date( 'm/d/Y', $ad->expTime );
      }
   }

}

//__END_OF_FILE__
