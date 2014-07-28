<?php

namespace lzx\html;

use lzx\core\Logger;
use lzx\html\HTMLElement;
use lzx\core\Controller;

class Template
{

   const UMODE_PC = 'pc';
   const UMODE_MOBILE = 'mobile';
   const UMODE_ROBOT = 'robot';
   const UROLE_GUEST = 'guest';
   const UROLE_USER = 'user';
   const UROLE_ADM = 'adm';
   const EVEN_ODD_CLASS = 'js_even_odd_parent';

   public static $path;
   public static $theme;
   public static $language;
   public static $debug = FALSE;
   private static $_status = TRUE;

   /**
    * @var Logger $logger
    * @static Logger $logger
    */
   private static $_logger = NULL;
   //private static $tpl_cache = []; // pool for rendered templates without $var
   public $tpl;
   public $var = [ ]; // controller need to fill this array (or an array Theme can access)
   private $_errors = [ ];
   private $_observers;

   /**
    * Observer design pattern interfaces
    */
   public function attach( Controller $observer )
   {
      $this->_observers->attach($observer);;
   }

   public function detach( Controller $observer )
   {
      $this->_observers->detach($observer);
   }

   public function notify()
   {
      foreach ( $this->_observers as $observer )
      {
         $observer->update( $this );
      }
   }

   /**
    * 
    * Constructor
    */
   public function __construct( $tpl, $var = [ ] )
   {
      $this->_observers = new \SplObjectStorage();

      $this->tpl = $tpl;
      if ( !empty( $var ) )
      {
         $this->var = $var;
      }
   }

   public function __toString()
   {
      // notify observers
      $this->notify();
      
      try
      {
         if ( $this->_errors )
         {
            $errors = $this->_errors;
            $tpl = 'error';
         }
         else
         {
            \extract( $this->var );
            $tpl = $this->tpl;
         }

         $tpl_theme = self::$theme;
         $tpl_path = self::$path . '/' . self::$theme;

         $tpl_file = $tpl_path . '/' . $tpl . '.tpl.php';
         if ( !\is_file( $tpl_file ) || !\is_readable( $tpl_file ) )
         {
            self::$_status = FALSE;
            $output = 'template loading error: [' . $tpl_theme . ':' . $tpl . ']';
         }
         else
         {
            $debug = self::$debug;
            $umode_pc = self::UMODE_PC;
            $umode_mobile = self::UMODE_MOBILE;
            $umode_robot = self::UMODE_ROBOT;
            $urole_guest = self::UROLE_GUEST;
            $urole_user = self::UROLE_USER;
            $urole_adm = self::UROLE_ADM;

            \ob_start();                 // Start output buffering
            include $tpl_file;      // Include the template file
            $output = \ob_get_contents();   // Get the contents of the buffer
            \ob_end_clean();                // End buffering and discard
         }
      }
      catch ( \Exception $e )
      {
         \ob_end_clean();
         self::$_status = FALSE;
         if ( isset( self::$_logger ) )
         {
            self::$_logger->error( $e->getMessage(), $e->getTrace() );
         }
         $output = 'template parsing error: [' . $tpl_theme . ':' . $tpl . ']';
      }

      return $output;
   }

   public static function setLogger( Logger $logger )
   {
      self::$_logger = $logger;
   }

   public static function getStatus()
   {
      return self::$_status;
   }

   public function error( $msg )
   {
      $this->_errors[] = $msg;
      self::$_status = FALSE;
   }

   public function formatTime( $timestamp )
   {
      return \date( 'm/d/Y H:i', $timestamp );
   }

   public function truncate( $str, $len = 45 )
   {
      if ( \strlen( $str ) < $len / 2 )
      {
         return $str;
      }
      $mb_len = \mb_strlen( $str );
      $rate = \sqrt( $mb_len / \strlen( $str ) ); // sqrt(0.7) = 0.837
      $s_len = ( $rate > 0.837 ? \ceil( $len * $rate ) : \floor( ($len - 2) * $rate ) );
      // the cut_off length is depend on the rate of non-single characters
      //var_dump(implode(' - ', [\strlen($str), $mb_len, $s_len, $rate, $str,  \mb_substr($str, 0, $s_len))));
      return ($mb_len > $s_len) ? \mb_substr( $str, 0, $s_len ) : $str;
   }

// local time function. do not touch them
// the following two functions convert between standard TIMESTAMP and local time
// we only store timestamp in database, for query and comparation
// we only display local time based on timezones
// do not use T in format, timezone info is not correct
   public function localDate( $format, $timestamp = TIMESTAMP )
   {
      return \date( $format, TIMESTAMP + ($_COOKIE[ 'timezone' ] - SYSTIMEZONE) * 3600 );
   }

// do not use timezone info in the $time string
   public function localStrToTime( $time )
   {
      return (\strtotime( $time ) - ($_COOKIE[ 'timezone' ] - SYSTIMEZONE) * 3600);
   }

   public function renderPage()
   {
      $css = '';
      foreach ( $this->css as $i )
      {
         $css .= '<link rel="stylesheet" media="all" href="' . $i . '" />';
      }
      $this->var[ 'head_css' ] = $css;

      $js = '';
      foreach ( $this->js as $i )
      {
         $js .= '<script src="' . $i . '"></script>';
      }
      $this->var[ 'head_js' ] = $js;

      return $this->render( 'html' );
   }

   /**
    *
    * @param type $url
    * @param type $type 'html' or 'json' data
    * @return type
    */
   /*
     public function ajax($url, $js_callback)
     {
     $attributes = [
     'class' => 'ajax_action',
     'style' => 'display: none;',
     'rel' => 'nofollow',
     'href' => $url,
     'title' => $js_callback
     );
     $link = new HTMLElement('a', '', $attributes);
     $callback = '<script type="text/javascript">' . $this->loadJS('ajax/' . $js_callback) . '</script>';
     return $link . $callback;
     }
    *
    */

   // text link

   /**
    *
    * @param type $name
    * @param type $url
    * @param array $attributes
    * @return \lzx\core\HTMLElement
    */
   public function link( $name, $url, array $attributes = [ ] )
   {
      $attributes[ 'href' ] = $url;
      return new HTMLElement( 'a', $name, $attributes );
   }

   // a list of text links

   /**
    *
    * @param array $list
    * @param array $attributes
    * @param boolean $even_odd
    * @return \lzx\core\HTMLElement
    */
   public function ulist( array $list, array $attributes = [ ], $even_odd = TRUE )
   {
      if ( $even_odd )
      {
         if ( \array_key_exists( 'class', $attributes ) )
         {
            $attributes[ 'class' ] .= ' ' . self::EVEN_ODD_CLASS;
         }
         else
         {
            $attributes[ 'class' ] = self::EVEN_ODD_CLASS;
         }
      }
      return new HTMLElement( 'ul', $this->_li( $list ), $attributes );
   }

   /**
    *
    * @param array $list
    * @param array $attributes
    * @return \lzx\core\HTMLElement
    */
   public function olist( array $list, array $attributes = [ ] )
   {
      return new HTMLElement( 'ol', $this->_li( $list ), $attributes );
   }

   private function _li( $list )
   {
      $_list = [ ];
      foreach ( $list as $li )
      {
         if ( \is_string( $li ) || $li instanceof HTMLElement )
         {
            $_list[] = new HTMLElement( 'li', $li );
         }
         elseif ( \is_array( $li ) )
         {
            if ( !\array_key_exists( 'text', $li ) )
            {
               throw new \Exception( 'list data is not found (missing "text" value in array)' );
            }
            elseif ( !\array_key_exists( 'attributes', $li ) )
            {
               throw new \Exception( 'list attributes is not found (missing "attributes" value in array)' );
            }
            else
            {
               $_list[] = new HTMLElement( 'li', $li[ 'text' ], $li[ 'attributes' ] );
            }
         }
      }
      return $_list;
   }

   public function dlist( array $list, array $attributes = [ ] )
   {
      $dl = new HTMLElement( 'dl', NULL, $attributes );
      foreach ( $list as $li )
      {
         if ( !is_array( $li ) || \sizeof( $li ) < 2 )
         {
            throw new \Exception( '$list need to be an array with dt and dd data' );
         }
         $dl->addElements( [new HTMLElement( 'dt', $li[ 'dt' ] ), new HTMLElement( 'dd', (string) $li[ 'dd' ] ) ] );
      }
      return $dl;
   }

   /**
    *
    * @param array $links
    * @param string $active_link
    * @return HTMLElement
    */
   public function linkList( array $links, $active_link = NULL )
   {
      $list = [ ];
      foreach ( $links as $link => $text )
      {
         if ( $link == $active_link )
         {
            $list[] = [
               'text' => $this->link( $text, $link ),
               'attributes' => ['class' => 'active' ],
            ];
         }
         else
         {
            $list[] = $this->link( $text, $link );
         }
      }

      return $this->ulist( $list, ['class' => 'tabs' ], FALSE );
   }

   /**
    *
    * @param array $data
    * @param array $attributes
    * @param type $even_odd
    * @return \lzx\core\HTMLElement
    * @throws \Exception
    *
    * $data = [
    *    'caption' => string / HTMLElement('*'),
    *    'thead' => $tr,
    *    'tfoot' => $tr,
    *    'tbody' => [$tr),
    * );
    * $tr = [
    *    'attributes' => [],
    *    'cells' => [$td),
    * );
    * $td = string / HTMLElement('*');
    * $td = [
    *    'attributes' => [],
    *    'text' => string
    * );
    */
   public function table( array $data, array $attributes = [ ], $even_odd = TRUE )
   {
      $table = new HTMLElement( 'table', NULL, $attributes );
      if ( array_key_exists( 'caption', $data ) && strlen( $data[ 'caption' ] ) > 0 )
      {
         $table->addElement( new HTMLElement( 'caption', $data[ 'caption' ] ) );
      }
      if ( array_key_exists( 'thead', $data ) && \sizeof( $data[ 'thead' ] ) > 0 )
      {
         $table->addElement( new HTMLElement( 'thead', self::_table_row( $data[ 'thead' ], TRUE ) ) );
      }
      if ( array_key_exists( 'tfoot', $data ) && \sizeof( $data[ 'tfoot' ] ) > 0 )
      {
         $table->addElement( new HTMLElement( 'tfoot', self::_table_row( $data[ 'tfoot' ] ) ) );
      }
      if ( !array_key_exists( 'tbody', $data ) )
      {
         throw new \Exception( 'table body (tbody) data is not found' );
      }

      $tbody_attr = $even_odd ? ['class' => self::EVEN_ODD_CLASS ] : [ ];

      $tbody = new HTMLElement( 'tbody', NULL, $tbody_attr );

      foreach ( $data[ 'tbody' ] as $tr )
      {
         $tbody->addElement( self::_table_row( $tr ) );
      }

      $table->addElement( $tbody );

      return $table;
   }

   private function _table_row( $row, $isHeader = FALSE )
   {
      if ( !\array_key_exists( 'cells', $row ) )
      {
         throw new \Exception( 'row cells (tr) data is not found' );
      }
      if ( array_key_exists( 'attributes', $row ) )
      {
         $tr = new HTMLElement( 'tr', NULL, $row[ 'attributes' ] );
      }
      else
      {
         $tr = new HTMLElement( 'tr', NULL );
      }

      $tag = $isHeader ? 'th' : 'td';
      foreach ( $row[ 'cells' ] as $td )
      {
         if ( \is_string( $td ) || $td instanceof HTMLElement )
         {
            $tr->addElement( new HTMLElement( $tag, $td ) );
         }
         elseif ( \is_array( $td ) )
         {
            if ( !\array_key_exists( 'text', $td ) )
            {
               throw new \Exception( 'cell data is not found (missing "text" value in array)' );
            }

            if ( \array_key_exists( 'attributes', $td ) )
            {
               $tr->addElement( new HTMLElement( $tag, $td[ 'text' ], $td[ 'attributes' ] ) );
            }
            else
            {
               $tr->addElement( new HTMLElement( $tag, $td[ 'text' ] ) );
            }
         }
      }
      return $tr;
   }

   /*
    * $form
    */

   public function form( $inputs, $action, $method = 'get', $attributes = [ ] )
   {
      //input text, radio, checkbox, textarea,
      $attributes[ 'action' ] = $action;
      $attributes[ 'method' ] = \in_array( $method, ['get', 'post' ] ) ? $method : 'get';
   }

   /*
    * [
    *    'name' => $name,
    *    'label' => $label,
    *    'class' => $class,
    *    'help' => $help,
    *    'attributes' => [
    *       'class' => $class,
    *       'required' => $required,
    *
    * form:
    * //fieldset - legend
    * //input - label [required, helper] // id = name
    * //textarea - label [required, helper]
    * // select -option -optgroup -label [required, helper]
    * // button [submit, reset]
    *    )
    *
    * textInput
    * checkboxInput
    * radioInput
    * emailInput
    * passwordInput
    * hiddenInput
    * fileInput
    * );
    */

   public function select( $name, $type, $label, array $options, $attributes )
   {
      if ( $type == 'checkbox' || $type == 'radio' )
      {
         $list = new HTMLElement( 'ul', NULL, ['class' => 'select_options' ] );
         $i = 0;
         foreach ( $options as $op )
         {
            $i++;
            $option = new HTMLElement( 'li' );
            $input_id = \implode( '_', [$type, $name, $i ] );
            $input_attr = [
               'id' => $input_id,
               'type' => $type,
               'name' => $name,
               'value' => $op[ 'value' ]
            ];
            $option->addElement( new HTMLElement( 'input', NULL, $input_attr ) );
            $option->addElement( new HTMLElement( 'label', $op[ 'text' ], ['for' => $input_id ] ) );
            $list->addElement( $option );
         }
      }
   }

   public function input( $name, $type, $label = '', $attributes = [ ] )
   {
      if ( $type == 'radio' || $type == 'checkbox' )
      {
         return new HTMLElement( 'ul' );
      }
      $label_div = new HTMLElement( 'div', new HTMLElement( 'label', $label, ['for' => $name ] ) );
      if ( array_key_exists( 'title', $attributes ) )
      {
         //$label_div->data = []
      }
      $input_div = new HTMLElement( 'div', new HTMLElement( 'input', NULL, $attributes ), ['class' => 'input_div' ] );
      //  <div>
      //        <input id="element_1" name="element_1" class="element text medium" type="text" maxlength="255" value=""/>
      //  </div>
   }

   public function uri( array $args = [ ], array $get = [ ] )
   {
      $conditions = [ ];
      foreach ( $get as $k => $v )
      {
         $conditions[] = $k . '=' . $v;
      }
      $query = \implode( '&', $conditions );

      return \htmlspecialchars( '/' . \implode( '/', $args ) . ($query ? '?' . $query : '') );
   }

   public function breadcrumb( array $links )
   {
      $list = [ ];
      $current = \array_pop( $links );
      foreach ( $links as $i )
      {
         $list[] = $this->link( $i[ 'name' ], $i[ 'href' ], ['title' => $i[ 'title' ] ] );
      }
      $list[] = $current[ 'name' ];

      return new HTMLElement( 'div', \implode( ' > ', $list ), ['class' => 'breadcrumb' ] );
   }

   public function pager( $pageNo, $pageCount, $uri )
   {
      if ( $pageCount < 2 )
      {
         return NULL;
      }

      if ( $pageCount <= 7 )
      {
         $pageFirst = 1;
         $pageLast = $pageCount;
      }
      else
      {
         $pageFirst = $pageNo - 3;
         $pageLast = $pageNo + 3;
         if ( $pageFirst < 1 )
         {
            $pageFirst = 1;
            $pageLast = 7;
         }
         elseif ( $pageLast > $pageCount )
         {
            $pageFirst = $pageCount - 6;
            $pageLast = $pageCount;
         }
      }

      if ( $pageNo != 1 )
      {
         $pager[] = ['text' => $this->link( '<<', $uri ), 'attributes' => ['class' => 'pageFirst' ] ];
         $pager[] = ['text' => $this->link( '<', $uri . '?page=' . ($pageNo - 1) ), 'attributes' => ['class' => 'pagePrevious' ] ];
      }
      for ( $i = $pageFirst; $i <= $pageLast; $i++ )
      {
         if ( $i == $pageNo )
         {
            $pager[] = ['text' => $this->link( (string) $i, $uri . '?page=' . $i ), 'attributes' => ['class' => 'pageActive' ] ];
         }
         else
         {
            $pager[] = $this->link( (string) $i, $uri . '?page=' . $i );
         }
      }
      if ( $pageNo != $pageCount )
      {
         $pager[] = ['text' => $this->link( '>', $uri . '?page=' . ($pageNo + 1) ), 'attributes' => ['class' => 'pageNext' ] ];
         $pager[] = ['text' => $this->link( '>>', $uri . '?page=' . $pageCount ), 'attributes' => ['class' => 'pageLast' ] ];
      }

      $pager = $this->ulist( $pager, ['class' => 'pager' ], FALSE );

      return $pager;
   }

}

//__END_OF_FILE__