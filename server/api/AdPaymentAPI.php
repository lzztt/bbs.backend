<?php

namespace site\api;

use site\Service;
use site\dbobject\AD;
use site\dbobject\ADPayment;

class AdPaymentAPI extends Service
{

   /**
    * get payments 
    * uri: /api/adpayment
    */
   public function get()
   {
      if ( $this->request->uid != 1 )
      {
         $this->forbidden();
      }
      
      $ad = new AD();
      $a_month_ago = $this->request->timestamp - 2592000;
      $this->_json( $ad->getAllAdPayments( $a_month_ago ) );
   }

   /**
    * create ad payment 
    * uri: /api/adpayment?action=post
    * post: ad_id=<ad_id>&amount=<amount>&time=<time>&ad_time=<ad_time>&comment<comment>
    */
   public function post()
   {
      if ( $this->request->uid != 1 )
      {
         $this->forbidden();
      }
      
      $ad = new AD();
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

      $this->_json( ['adName' => $ad->name, 'amount' => $ap->amount, 'expTime' => $ad->expTime ] );
   }

}
