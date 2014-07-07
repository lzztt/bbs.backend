<?php

namespace site\controller\iostat;

use site\controller\IOstat;
use lzx\html\HTMLElement;
use lzx\html\Template;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ad
 *
 * @author ikki
 */
class IOstatCtrler extends IOstat
{

   public function run()
   {
      
      $this->cache->setStatus( FALSE );

      $this->html->var['content'] = $this->sarchart();
   }

   public function sarchart()
   {
      $sar = 'sar -b -s 00:00:01 -e 23:59:59 -f ';
      $file = '/var/log/sysstat/sa' . date( 'd' );

      // using an old date file
      if ( $this->args[1] )
      {
         $_file = '/var/log/sysstat/sa' . $this->args[1];
         if ( is_file( $_file ) && is_readable( $_file ) )
         {
            $file = $_file;
         }
      }

      // check the date file
      if ( !(is_file( $file ) && is_readable( $file )) )
      {
         $this->error( 'io stat data does not exist' );
      }

      // every 10 minutes = 600 seconds
      $sar = $sar . $file . ' 600';
      $cmd = $sar . ' | grep -E \'^[0-9]{2}:[0-9]{2}:[0-9]{2}\' | grep -v tps | awk \'{print "[["$1",0], " $2", "$3"],"}\' | sed \'s/:/,/g\' > /tmp/iodata.txt';
      shell_exec( $cmd );

      $content = [
         'data' => file_get_contents( '/tmp/iodata.txt' ),
         'start' => shell_exec( 'head -n 1 /tmp/iodata.txt | awk \'{print $1}\' | cut -c 2-13' ),
         'end' => shell_exec( 'tail -n 1 /tmp/iodata.txt | awk \'{print $1}\' | cut -c 2-13' ),
         'max' => shell_exec( 'sort -nr -k 2 /tmp/iodata.txt | head -n 1 | awk \'{print $2}\'' ),
      ];
//var_dump($content);exit;
      return (new Template( 'iochart', $content ));
   }

   public function header()
   {
      return 'header';
   }

   public function cancel()
   {
      return 'cancel';
   }

   public function success()
   {
      return 'success';
   }

}

//__END_OF_FILE__
