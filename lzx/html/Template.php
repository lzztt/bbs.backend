<?php

namespace lzx\html;

use lzx\core\Logger;
use lzx\html\HTMLElement;

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
   private static $status = TRUE;

   /**
    * @var Logger $logger
    * @static Logger $logger
    */
   private static $logger = NULL;
   //private static $tpl_cache = array(); // pool for rendered templates without $var
   public $tpl;
   public $var = array( ); // controller need to fill this array (or an array Theme can access)

//   public $css = array();
//   public $js = array();

   public function __construct( $tpl, $var = array( ) )
   {
      $this->tpl = $tpl;
      if ( !empty( $var ) )
      {
         $this->var = $var;
      }
   }

   public function __toString()
   {
      try
      {
         \extract( $this->var );
         $tpl_theme = self::$theme;
         $tpl_path = self::$path . '/' . self::$theme;
         $umode_pc = self::UMODE_PC;
         $umode_mobile = self::UMODE_MOBILE;
         $umode_robot = self::UMODE_ROBOT;
         $urole_guest = self::UROLE_GUEST;
         $urole_user = self::UROLE_USER;
         $urole_adm = self::UROLE_ADM;

         \ob_start();                 // Start output buffering
         include $tpl_path . '/' . $this->tpl . '.tpl.php';      // Include the template file
         $output = \ob_get_contents();   // Get the contents of the buffer
         \ob_end_clean();                // End buffering and discard
      }
      catch ( \Exception $e )
      {
         \ob_end_clean();
         if ( isset( self::$logger ) )
         {
            self::$logger->error( $e->getMessage() );
         }
         $output = '[longzox template error]';

         self::$status = FALSE;
         //$output .= \nl2br(\print_r($e, TRUE));
      }

      return $output;
   }

   public static function setLogger( Logger $logger )
   {
      self::$logger = $logger;
   }

   public static function getStatus()
   {
      return self::$status;
   }

   /*
     public function loadJS($js)
     {
     return \file_get_contents($this->js_path . '/' . $js . '.js');
     }
    *
    */

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
      //var_dump(implode(' - ', array(\strlen($str), $mb_len, $s_len, $rate, $str,  \mb_substr($str, 0, $s_len))));
      return ($mb_len > $s_len) ? \mb_substr( $str, 0, $s_len ) : $str;
   }

// local time function. do not touch them
// the following two functions convert between standard TIMESTAMP and local time
// we only store timestamp in database, for query and comparation
// we only display local time based on timezones
// do not use T in format, timezone info is not correct
   public function localDate( $format, $timestamp = TIMESTAMP )
   {
      return date( $format, TIMESTAMP + ($_COOKIE['timezone'] - SYSTIMEZONE) * 3600 );
   }

// do not use timezone info in the $time string
   public function localStrToTime( $time )
   {
      return (strtotime( $time ) - ($_COOKIE['timezone'] - SYSTIMEZONE) * 3600);
   }

   public function renderPage()
   {
      $css = '';
      foreach ( $this->css as $i )
      {
         $css .= '<link rel="stylesheet" media="all" href="' . $i . '" />';
      }
      $this->var['head_css'] = $css;

      $js = '';
      foreach ( $this->js as $i )
      {
         $js .= '<script src="' . $i . '"></script>';
      }
      $this->var['head_js'] = $js;

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
     $attributes = array(
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
   public function link( $name, $url, array $attributes = array( ) )
   {
      $attributes['href'] = $url;
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
   public function ulist( array $list, array $attributes = array( ), $even_odd = TRUE )
   {
      if ( $even_odd )
      {
         if ( \array_key_exists( 'class', $attributes ) )
         {
            $attributes['class'] .= ' ' . self::EVEN_ODD_CLASS;
         }
         else
         {
            $attributes['class'] = self::EVEN_ODD_CLASS;
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
   public function olist( array $list, array $attributes = array( ) )
   {
      return new HTMLElement( 'ol', $this->_li( $list ), $attributes );
   }

   private function _li( $list )
   {
      $_list = array( );
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
               $_list[] = new HTMLElement( 'li', $li['text'], $li['attributes'] );
            }
         }
      }
      return $_list;
   }

   public function dlist( array $list, array $attributes = array( ) )
   {
      $dl = new HTMLElement( 'dl', NULL, $attributes );
      foreach ( $list as $li )
      {
         if ( !is_array( $li ) || \sizeof( $li ) < 2 )
         {
            throw new \Exception( '$list need to be an array with dt and dd data' );
         }
         $dl->addElements( new HTMLElement( 'dt', $li['dt'] ), new HTMLElement( 'dd', (string) $li['dd'] ) );
      }
      return $dl;
   }

   /**
    *
    * @param array $links
    * @param string $active_link
    * @return HTMLElement
    */
   public function linkTabs( array $links, $active_link = NULL )
   {
      $list = array( );
      foreach ( $links as $link => $text )
      {
         if ( $link == $active_link )
         {
            $list[] = array(
               'text' => $this->link( $text, $link ),
               'attributes' => array( 'class' => 'active' ),
            );
         }
         else
         {
            $list[] = $this->link( $text, $link );
         }
      }

      return $this->ulist( $list, array( 'class' => 'tabs' ), FALSE );
   }

   /**
    *
    * @param array $data
    * @param array $attributes
    * @param type $even_odd
    * @return \lzx\core\HTMLElement
    * @throws \Exception
    *
    * $data = array(
    *    'caption' => string / HTMLElement('*'),
    *    'thead' => $tr,
    *    'tfoot' => $tr,
    *    'tbody' => array($tr),
    * );
    * $tr = array(
    *    'attributes' => array(),
    *    'cells' => array($td),
    * );
    * $td = string / HTMLElement('*');
    * $td = array(
    *    'attributes' => array(),
    *    'text' => string
    * );
    */
   public function table( array $data, array $attributes = array( ), $even_odd = TRUE )
   {
      $table = new HTMLElement( 'table', NULL, $attributes );
      if ( array_key_exists( 'caption', $data ) && strlen( $data['caption'] ) > 0 )
      {
         $table->addElements( new HTMLElement( 'caption', $data['caption'] ) );
      }
      if ( array_key_exists( 'thead', $data ) && \sizeof( $data['thead'] ) > 0 )
      {
         $table->addElements( new HTMLElement( 'thead', self::_table_row( $data['thead'], TRUE ) ) );
      }
      if ( array_key_exists( 'tfoot', $data ) && \sizeof( $data['tfoot'] ) > 0 )
      {
         $table->addElements( new HTMLElement( 'tfoot', self::_table_row( $data['tfoot'] ) ) );
      }
      if ( !array_key_exists( 'tbody', $data ) )
      {
         throw new \Exception( 'table body (tbody) data is not found' );
      }

      $tbody_attr = $even_odd ? array( 'class' => self::EVEN_ODD_CLASS ) : array( );

      $tbody = new HTMLElement( 'tbody', NULL, $tbody_attr );

      foreach ( $data['tbody'] as $tr )
      {
         $tbody->addElements( self::_table_row( $tr ) );
      }

      $table->addElements( $tbody );

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
         $tr = new HTMLElement( 'tr', NULL, $row['attributes'] );
      }
      else
      {
         $tr = new HTMLElement( 'tr', NULL );
      }

      $tag = $isHeader ? 'th' : 'td';
      foreach ( $row['cells'] as $td )
      {
         if ( \is_string( $td ) || $td instanceof HTMLElement )
         {
            $tr->addElements( new HTMLElement( $tag, $td ) );
         }
         elseif ( \is_array( $td ) )
         {
            if ( !\array_key_exists( 'text', $td ) )
            {
               throw new \Exception( 'cell data is not found (missing "text" value in array)' );
            }

            if ( \array_key_exists( 'attributes', $td ) )
            {
               $tr->addElements( new HTMLElement( $tag, $td['text'], $td['attributes'] ) );
            }
            else
            {
               $tr->addElements( new HTMLElement( $tag, $td['text'] ) );
            }
         }
      }
      return $tr;
   }

   /*
    * $form
    */

   public function form( $inputs, $action, $method = 'get', $attributes = array( ) )
   {
      //input text, radio, checkbox, textarea,
      $attributes['action'] = $action;
      $attributes['method'] = \in_array( $method, array( 'get', 'post' ) ) ? $method : 'get';
   }

   /*
    * array(
    *    'name' => $name,
    *    'label' => $label,
    *    'class' => $class,
    *    'help' => $help,
    *    'attributes' => array(
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
         $list = new HTMLElement( 'ul', NULL, array( 'class' => 'select_options' ) );
         $i = 0;
         foreach ( $options as $op )
         {
            $i++;
            $option = new HTMLElement( 'li' );
            $input_id = \implode( '_', array( $type, $name, $i ) );
            $input_attr = array(
               'id' => $input_id,
               'type' => $type,
               'name' => $name,
               'value' => $op['value']
            );
            $option->addElements( new HTMLElement( 'input', NULL, $input_attr ) );
            $option->addElements( new HTMLElement( 'label', $op['text'], array( 'for' => $input_id ) ) );
            $list->addElements( $option );
         }
      }
   }

   public function input( $name, $type, $label = '', $attributes = array( ) )
   {
      if ( $type == 'radio' || $type == 'checkbox' )
      {
         return new HTMLElement( 'ul' );
      }
      $label_div = new HTMLElement( 'div', new HTMLElement( 'label', $label, array( 'for' => $name ) ) );
      if ( array_key_exists( 'title', $attributes ) )
      {
         //$label_div->data = array()
      }
      $input_div = new HTMLElement( 'div', new HTMLElement( 'input', NULL, $attributes ), array( 'class' => 'input_div' ) );
      //  <div>
      //        <input id="element_1" name="element_1" class="element text medium" type="text" maxlength="255" value=""/>
      //  </div>
   }

   public function uri( array $args = array( ), array $get = array( ) )
   {
      $conditions = array( );
      foreach ( $get as $k => $v )
      {
         $conditions[] = $k . '=' . $v;
      }
      $query = \implode( '&', $conditions );

      return \htmlspecialchars( '/' . \implode( '/', $args ) . ($query ? '?' . $query : '') );
   }

   public function generatePager( $pageNo, $pageCount, $uri )
   {
      if ( $pageNo > $pageCount || $pageNo === 'last' )
      {
         $pageNo = $pageCount;
      }
      if ( $pageNo < 1 )
      {
         $pageNo = 1;
      }

      if ( $pageCount < 2 )
      {
         return array( $pageNo, null );
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
         $pager[] = array( 'text' => $this->link( '<<', $uri ), 'attributes' => array( 'class' => 'pageFirst' ) );
         $pager[] = array( 'text' => $this->link( '<', $uri . '?page=' . ($pageNo - 1) ), 'attributes' => array( 'class' => 'pagePrevious' ) );
      }
      for ( $i = $pageFirst; $i <= $pageLast; $i++ )
      {
         if ( $i == $pageNo )
         {
            $pager[] = array( 'text' => $this->link( (string) $i, $uri . '?page=' . $i ), 'attributes' => array( 'class' => 'pageActive' ) );
         }
         else
         {
            $pager[] = $this->link( (string) $i, $uri . '?page=' . $i );
         }
      }
      if ( $pageNo != $pageCount )
      {
         $pager[] = array( 'text' => $this->link( '>', $uri . '?page=' . ($pageNo + 1) ), 'attributes' => array( 'class' => 'pageNext' ) );
         $pager[] = array( 'text' => $this->link( '>>', $uri . '?page=' . $pageCount ), 'attributes' => array( 'class' => 'pageLast' ) );
      }

      $pager = $this->ulist( $pager, array( 'class' => 'pager' ), FALSE );

      return array( $pageNo, $pager );
   }

}

//__END_OF_FILE__