<?php

namespace site\controller\adm;

use site\controller\Adm;
use lzx\html\Template;
use site\dbobject\AD;
use site\dbobject\ADPayment;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of adm
 *
 * @author ikki
 */
class AdCtrler extends Adm
{

   public function run()
   {
      $function = $this->args[ 0 ] ? $this->args[ 0 ] : 'show';

      if ( \method_exists( $this, $function ) )
      {
         $this->html->var[ 'content' ] = $this->$function();
      }
      else
      {
         $this->request->pageNotFound( 'action not found :(' );
      }
   }

   public function show()
   {
      $ad = new AD();
      $a_month_ago = $this->request->timestamp - 2592000;
      $contents = [
         'ads' => $ad->getAllAds( $a_month_ago ),
         'payments' => $ad->getAllAdPayments( $a_month_ago )
      ];
      return new Template( 'ads', $contents );
   }

   public function add()
   {
      $ad = new AD();
      // show add form
      if ( \sizeof( $this->request->post ) == 0 )
      {
         return new Template( 'ad_new' );
      }
      // add record
      else
      {
         $ad->name = $this->request->post[ 'name' ];
         $ad->email = $this->request->post[ 'email' ];
         $ad->typeID = $this->request->post[ 'type_id' ];
         $ad->expTime = $this->request->timestamp;
         $ad->add();
         return '广告添加成功: ' . $ad->name . ' : ' . $ad->email;
      }
   }

   public function payment()
   {
      $ad = new AD();
      // show add form
      if ( \sizeof( $this->request->post ) == 0 )
      {
         return new Template( 'ad_payment_new', ['ads' => $ad->getList( 'id,name' ), 'date' => \date( 'm/d/Y', $this->request->timestamp ) ] );
      }
      // add record
      else
      {
         $ap = new ADPayment();
         $ap->adID = $this->request->post[ 'ad_id' ];
         $ap->amount = $this->request->post[ 'amount' ];
         $ap->time = \strtotime( $this->request->post[ 'time' ] );
         $ap->comment = $this->request->post[ 'comment' ];
         $ap->add();

         $ad->id = $ap->adID;
         $ad->load( 'name,expTime' );
         if ( $ad->expTime < $this->request->timestamp )
         {
            $exp_time = $this->request->post[ 'time' ];
         }
         else
         {
            $exp_time = \date( 'm/d/Y', $ad->expTime );
         }
         $ad->expTime = \strtotime( $exp_time . ' +' . $this->request->post[ 'ad_time' ] . ' months' );
         $ad->update( 'expTime' );

         return '付款添加成功: ' . $ad->name . ' : $' . $ap->amount . '<br>广告有效期更新至: ' . \date( 'm/d/Y', $ad->expTime );
      }
   }

}

//__END_OF_FILE__