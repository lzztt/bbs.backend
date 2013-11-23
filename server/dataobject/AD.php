<?php

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\MySQL;

/**
 * @property $id
 * @property $name
 * @property $type_id
 * @property $exp_time
 * @property $email
 */
class AD extends DataObject
{

   public function __construct( $load_id = null, $fields = '' )
   {
      $db = MySQL::getInstance();
      $table = \array_pop( \explode( '\\', __CLASS__ ) );
      parent::__construct( $db, $table, $load_id, $fields );
   }

   public function getAllAds( $from_time = 0 )
   {
      $where = $from_time > 0 ? ('WHERE exp_time > ' . $from_time) : '';
      return $this->_db->select( 'SELECT * FROM AD ' . $where . ' ORDER BY exp_time' );
   }

   public function getAllAdPayments( $from_time = 0 )
   {
      $where = 'WHERE adp.id in ( SELECT max(adp.id) FROM ADPayment adp JOIN AD ad ON adp.ad_id = ad.id ' . ( $from_time > 0 ? ('WHERE ad.exp_time > ' . $from_time) : '' )  . ' GROUP BY ad_id )';
      return $this->_db->select( 'SELECT adp.id, ad.name, adp.amount, adp.time AS pay_time, ad.exp_time, adp.comment FROM ADPayment adp LEFT JOIN AD ad ON adp.ad_id = ad.id ' . $where . ' ORDER BY adp.time DESC' );
   }
   
   public function getAllAdTypes()
   {
      return $this->_db->select('SELECT id, name FROM ADType');
   }

}

?>
