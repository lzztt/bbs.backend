<?php

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $name
 * @property $typeID
 * @property $expTime
 * @property $email
 */
class AD extends DBObject
{

   public function __construct( $id = null, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'ads';
      $fields = [
         'id' => 'id',
         'name' => 'name',
         'typeID' => 'type_id',
         'expTime' => 'exp_time',
         'email' => 'email'
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }

   public function getAllAds( $from_time = 0 )
   {
      $fields = [
         'id' => 'id',
         'name' => 'name',
         'typeID' => 'type_id',
         'expTime' => 'exp_time',
         'email' => 'email'
      ];

      return $this->_convertFields( $this->call( 'get_ads(' . $from_time . ')' ), $fields );
   }

   public function getAllAdPayments( $from_time = 0 )
   {
       $fields = [
         'id' => 'id',
         'name' => 'name', 
         'amount' => 'amount',
         'expTime' => 'exp_time',
         'payTime' => 'pay_time',
         'comment' => 'comment'
      ];
      return $this->_convertFields( $this->call( 'get_ad_payments(' . $from_time . ')' ), $fields);
   }

   public function getAllAdTypes()
   {
      return $this->call( 'get_ad_types()' );
   }

}

//__END_OF_FILE__
