<?php

namespace site;

use lzx\db\DB;

/**
 * Description of SessionDB
 *
 * @author ikki
 */
class Session
{

   private static $_cookieName = 'LZXSID';
   private $_isNew = FALSE;
   private $_db;
   private $_sid = NULL;
   private $_uid = 0;
   private $_cid = 0;
   private $_atime = 0;
   private $_data = [ ];
   private $_uidOriginal = 0;
   private $_cidOriginal = 0;
   private $_dataOriginal = [ ];

   // id is 15 charactors
   private function __construct( DB $db = NULL )
   {
      if ( $db )
      {
         if ( $_COOKIE[ self::$_cookieName ] )
         {
            // client has a session id
            $this->_sid = $_COOKIE[ self::$_cookieName ];
         }
         else
         {
            // client has no session id
            $this->_startNewSession();
         }

         $this->_db = $db;

         if ( !$this->_isNew )
         {
            // load session from database
            $arr = $db->query( 'SELECT * FROM sessions WHERE id = "' . $this->_sid . '"' );
            if ( $arr )
            {
               // validate session's user agent crc checksum
               if ( $this->_crc32() === (int) $arr[ 0 ][ 'crc' ] )
               {
                  // valid agent
                  $this->_uid = (int) $arr[ 0 ][ 'uid' ];
                  $this->_cid = (int) $arr[ 0 ][ 'cid' ];
                  $this->_atime = (int) $arr[ 0 ][ 'atime' ];

                  $this->_uidOriginal = $this->_uid;
                  $this->_cidOriginal = $this->_cid;

                  if ( $arr[ 0 ][ 'data' ] )
                  {
                     $this->_data = \json_decode( $arr[ 0 ][ 'data' ], TRUE );

                     if ( \is_array( $this->_data ) )
                     {
                        $this->_dataOriginal = $this->_data;
                     }
                     else
                     {
                        $this->_data = [ ];
                     }
                  }
               }
               else
               {
                  // invalid agent. this shouldn't happen!!
                  $this->_startNewSession();
               }
            }
            else
            {
               // no session found in database, start new session
               $this->_startNewSession();
            }
         }
      }
   }

   final public function __get( $key )
   {
      return \array_key_exists( $key, $this->_data ) ? $this->_data[ $key ] : NULL;
   }

   final public function __set( $key, $val )
   {
      if ( \is_null( $val ) )
      {
         unset( $this->$key );
      }
      else
      {
         $this->_data[ $key ] = $val;
      }
   }

   final public function __isset( $key )
   {
      return \array_key_exists( $key, $this->_data ) ? isset( $this->_data[ $key ] ) : FALSE;
   }

   final public function __unset( $key )
   {
      if ( \array_key_exists( $key, $this->_data ) )
      {
         unset( $this->_data[ $key ] );
      }
   }

   /**
    * Return the Session object
    *
    * @return Session
    */
   public static function getInstance( DB $db = NULL )
   {
      static $instance;

      if ( !isset( $instance ) )
      {
         $instance = new self( $db );
      }
      else
      {
         throw new \Exception( 'Session instance already exists, cannot create a new instance' );
      }

      return $instance;
   }

   public function getSessionID()
   {
      return $this->_sid;
   }

   public function getCityID()
   {
      return $this->_cid;
   }

   public function setCityID( $cid )
   {
      $this->_cid = (int) $cid;
   }

   public function getUserID()
   {
      return $this->_uid;
   }

   public function setUserID( $uid )
   {
      $this->_uid = (int) $uid;
   }

   public function clear()
   {
      $this->_uid = 0;
      $this->_data = [ ];
   }

   public function close()
   {
      if ( $this->_db )
      {
         if ( $this->_isNew )
         {
            // db insert for new session
            $this->_db->query( 'INSERT INTO sessions VALUES (:id, :data, :atime, :uid, :cid, :crc)', [
               ':id' => $this->_sid,
               ':data' => $this->_data ? \json_encode( $this->_data, \JSON_NUMERIC_CHECK | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE ) : '',
               ':atime' => $_SERVER[ 'REQUEST_TIME' ],
               ':uid' => $this->_uid,
               ':cid' => $this->_cid,
               ':crc' => $this->_crc32()
            ] );
         }
         else
         {
            // db update for existing session
            $fields = [ ];
            $values = [ ];

            if ( $this->_uid != $this->_uidOriginal )
            {
               $fields[] = 'uid=:uid';
               $values[ ':uid' ] = $this->_uid;
            }

            if ( $this->_cid != $this->_cidOriginal )
            {
               $fields[] = 'cid=:cid';
               $values[ ':cid' ] = $this->_cid;
            }

            if ( $this->_data != $this->_dataOriginal )
            {
               $fields[] = 'data=:data';
               $values[ ':data' ] = $this->_data ? \json_encode( $this->_data, \JSON_NUMERIC_CHECK | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE ) : '';
            }

            // update access timestamp older than 1 minute
            $time = (int) $_SERVER[ 'REQUEST_TIME' ];
            if ( $time - $this->_atime > 60 )
            {
               $fields[] = 'atime=:atime';
               $values[ ':atime' ] = $time;
            }

            if ( $fields )
            {
               $sql = 'UPDATE sessions SET ' . \implode( $fields, ',' ) . ' WHERE id = "' . $this->_sid . '"';
               $this->_db->query( $sql, $values );
            }
         }
      }
   }

   private function _startNewSession()
   {
      $this->_sid = \sprintf( "%02x", \mt_rand( 0, 255 ) ) . \uniqid();
      \setcookie( self::$_cookieName, $this->_sid, ((int) $_SERVER[ 'REQUEST_TIME' ] + 2592000 ), '/', '.' . \implode( '.', \array_slice( \explode( '.', $_SERVER[ 'HTTP_HOST' ] ), -2 ) ) );
      $this->_isNew = TRUE;
   }

   private function _crc32()
   {
      return \crc32( $_SERVER[ 'HTTP_USER_AGENT' ] . $this->_sid );
   }

}

//__END_OF_FILE__
