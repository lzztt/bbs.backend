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
      $where = $from_time > 0 ? ('WHERE exp_time > ' . $from_time) : '';
      return $this->_db->select( 'SELECT * FROM ads ' . $where . ' ORDER BY exp_time' );
   }

   public function getAllAdPayments( $from_time = 0 )
   {
      $where = 'WHERE adp.id in ( SELECT max(adp.id) FROM ad_payments adp JOIN ads ad ON adp.ad_id = ad.id ' . ( $from_time > 0 ? ('WHERE ad.exp_time > ' . $from_time) : '' ) . ' GROUP BY ad_id )';
      return $this->_db->select( 'SELECT adp.id, ad.name, adp.amount, adp.time AS pay_time, ad.exp_time, adp.comment FROM ad_payments adp LEFT JOIN ads ad ON adp.ad_id = ad.id ' . $where . ' ORDER BY adp.time DESC' );
   }

   public function getAllAdTypes()
   {
      return $this->_db->select( 'SELECT id, name FROM ad_types' );
   }

}

?>
