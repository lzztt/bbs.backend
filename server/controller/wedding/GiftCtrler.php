<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace site\controller\wedding;

use site\controller\Wedding;
use lzx\html\Template;
use site\dbobject\Wedding as WeddingAttendee;

/**
 * Description of Wedding
 *
 * @author ikki
 */
class GiftCtrler extends Wedding
{

   public function run()
   {
      Template::$theme = $this->config->theme[ 'wedding2' ];
      // login first
      if ( !$this->session->loginStatus )
      {
         $this->_displayLogin();
         return;
      }

      // logged in      
      $this->html->var[ 'navbar' ] = new Template( 'navbar' );
      $a = new WeddingAttendee();
      list($table_guests, $table_counts, $total) = $this->_getTableGuests( $a->getList( 'name,tid,gift,value,guests,comment' ), 'value' );

      $this->html->var[ 'body' ] = new Template( 'gifts', ['tables' => $table_guests, 'counts' => $table_counts, 'total' => $total ] );
   }

}
