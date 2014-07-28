<?php

namespace site\controller\iostat;

use site\controller\IOStat;
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
class IOStatCtrler extends IOStat
{

   public function run()
   {
      $this->error( 'not available yet :(' );
      $this->html->var[ 'content' ] = $this->sarChart();
   }

   protected function sarChart()
   {
      $sar = 'sar -b -s 00:00:01 -e 23:59:59 -f ';
      $file = '/var/log/sysstat/sa' . \date( 'd' );

      // using an old date file
      if ( $this->id )
      {
         $_file = '/var/log/sysstat/sa' . \sprintf( '%2d', $this->id );
         if ( \is_file( $_file ) && \is_readable( $_file ) )
         {
            $file = $_file;
         }
      }

      // check the date file
      if ( !(\is_file( $file ) && \is_readable( $file )) )
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

      return (new Template( 'iochart', $content ));
   }

}

//__END_OF_FILE__
