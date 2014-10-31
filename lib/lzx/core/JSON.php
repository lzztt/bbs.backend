<?php

namespace lzx\core;

/**
 * Description of JSON
 *
 * @author ikki
 */
class JSON
{

   private $_error = FALSE;
   private $_data = [ ];
   private $_string;

   public function __construct( array $data )
   {
      if ( $data )
      {
         $this->_data = $data;
      }
   }

   public function __toString()
   {
      if ( $this->_string )
      {
         return $this->_string;
      }

      if ( $this->error )
      {
         $this->_data[ 'error' ] = $this->_error;
      }

      $json = \json_encode( $return, \JSON_NUMERIC_CHECK | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE );
      if ( $json === FALSE )
      {
         $json = '{"error":"json encode error"}';
      }
   }

}

//__END_OF_FILE__
