<?php

namespace site\controller;

use site\Controller;
use lzx\html\Template;

class Weather extends Controller
{

   public function run()
   {

      $this->request->pageNotFound();

      parent::run();
      $this->checkAJAX();

      if ( (strlen( $this->request->args[1] ) == 5) && is_numeric( $this->request->args[1] ) && ($this->request->args[1] > 0) )
      {
         $zip = $this->request->args[1];
      }
      else
      {
         $zip = '77036';
      }
      $this->html->var['content'] = $this->get_weather( $zip );
   }

   public function get_weather_div( $zip )
   {
      try
      {
         return '<div style="float: right">' . $this->get_weather( $zip ) . '</div>';
      }
      catch ( \Exception $e )
      {
         $this->logger->error( $e->getMessage() );
         $this->cache->setStatus( FALSE );
         return '';
      }
   }

   public function get_weather( $zip )
   {
      //$this->cache->setStatus(FALSE);
      //$key = 'weather_' . $zip;
      //$data = $this->cache->fetch($key);
      //if ($data === FALSE)
      //{
      $weather = $this->get_weather_detail( $zip );
      $data = $this->display_weather( $weather );
      //$this->cache->store($key, $data);
      //}
      return $data;
   }

   public function display_weather( $weather )
   {
      $title = $weather[0];
      $days = $weather[1];
      $str = '<div><div style="padding: 3px; font-weight:bold;">' . $title . '</div><table id="weatherTable" style="margin: 0; border: 1px solid #E0E0E0; border-collapse: collapse;"><tbody>';
      $update = array_pop( $days );

      $i = 0;
      foreach ( $days as $day )
      {
         $tmp = explode( '/', $day[3] );
         foreach ( $tmp as &$v )
         {
            $v = (int) $v;
         }

         $day[3] = implode( '-', array_reverse( $tmp ) ) . ' &deg;F';

         $css = ($i % 2 == 0) ? '' : ' style="background-color: #FCF1D0;"';
         $str .= '<tr' . $css . '>';
         $str .= '<td style="text-align: right">' . trim( substr( $day[0], 0, -6 ) ) . '<br />' . substr( $day[0], -6 ) . '</td>';
         $str .= '<td><img src="' . $day[1] . '" /></td>';
         $str .= '<td>' . (strlen( $day[2] ) > 20 ? trim( substr( $day[2], 0, 20 ) ) : $day[2]) . '</td>';
         $str .= '<td style="text-align: right">' . $day[3] . '</td>';
         $str .= '</tr>';
         $i++;
      }

      $time = trim( $update[0] );
      $time = substr( $time, 0, -7 ) . str_replace( 'CT', date( 'T', $this->request->timestamp ), strtoupper( substr( $time, -7 ) ) ) . '</div>';
      $str .= '<tr><td colspan="4" style="text-align: right">' . $time . '</td></tr>';

      $str .= '</tbody></table>';
      $str .= '<style type="text/css">table#weatherTable td {padding: 2px 5px;}</style></div>';
      return $str;
   }

   public function get_weather_detail( $zip )
   {
      $doc = new \DOMDocument();
      // We need to validate our document before refering to the id
      $xml_error_internal = \libxml_use_internal_errors( TRUE );
      //$doc->validateOnParse = TRUE;
      @$doc->loadHtml( $this->request->curlGetData( 'http://www.weather.com/weather/print/' . $zip ) );
      //var_dump(\libxml_get_errors());
      \libxml_clear_errors();
      \libxml_use_internal_errors( $xml_error_internal );

      foreach ( $doc->getElementsByTagName( 'div' ) as $div )
      {
         if ( $div->getAttribute( 'class' ) == 'WRprintTitle' )
         {
            $title = $div->textContent;
            break;
         }
      }

      $table = $doc->getElementById( 'f2' );
      $trs = $table->getElementsByTagName( 'tr' );
      // remove table header
      $table->removeChild( $trs->item( 0 ) );

      //return $table->ownerDocument->saveXML($table);

      foreach ( $table->getElementsByTagName( 'tr' ) as $tr )
      {
         $i = 0;
         $arr = [];
         foreach ( $tr->getElementsByTagName( 'td' ) as $td )
         {
            $i++;

            if ( $i > 4 )
               continue;

            if ( $i == 2 )
            {
               $img = $td->getElementsByTagName( 'img' )->item( 0 )->getAttribute( 'src' );
               if ( strlen( $img ) > 0 )
                  $arr[] = $img;
               else
                  return;
            }
            else
            {
               $text = $td->textContent;
               if ( strlen( $text ) > 0 )
                  $arr[] = $text;
               else
                  return;
            }
         }
         $day[] = $arr;
      }

      return [ $title, $day ];
   }

}

//__END_OF_FILE__