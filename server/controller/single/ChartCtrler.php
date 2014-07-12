<?php

namespace site\controller\single;

use site\controller\Single;
use lzx\html\Template;

/**
 * @property \lzx\db\DB $db database object
 */
class ChartCtrler extends Single
{

   public function run()
   {
      $stat = [
         '七夕' => $this->_getAgeStatJSON( 1312350024, 1313607290 ),
         '得闲饮茶' => $this->_getAgeStatJSON( 1313941540, 1315549125 ),
         '有钱人' => $this->_getAgeStatJSON( 1331521805, 1332011793 ),
         '三十看从前' => $this->_getAgeStatJSON( 1363746005, 1376629987 ),
      ];

      $stat = [ ];

      $sanshi_two = $this->_getAgeStatJSON( 1376629987, 1463746005 );
      $stat[] = [
         [
            'title' => '三十看从前聚会(二) 女生 (' . $sanshi_two[ 0 ][ 'total' ] . ')人',
            'data' => $sanshi_two[ 0 ][ 'json' ],
            'div_id' => 'div_sanshi_two_female'
         ],
         [
            'title' => '三十看从前聚会(二) 男生 (' . $sanshi_two[ 1 ][ 'total' ] . ')人',
            'data' => $sanshi_two[ 1 ][ 'json' ],
            'div_id' => 'div_sanshi_two_male'
         ],
      ];

      $sanshi = $this->_getAgeStatJSON( 1363746005, 1376629987 );
      $stat[] = [
         [
            'title' => '三十看从前聚会 女生 (' . $sanshi[ 0 ][ 'total' ] . ')人',
            'data' => $sanshi[ 0 ][ 'json' ],
            'div_id' => 'div_sanshi_female'
         ],
         [
            'title' => '三十看从前聚会 男生 (' . $sanshi[ 1 ][ 'total' ] . ')人',
            'data' => $sanshi[ 1 ][ 'json' ],
            'div_id' => 'div_sanshi_male'
         ],
      ];

      $youqianren = $this->_getAgeStatJSON( 1331521805, 1332011793 );
      $stat[] = [
         [
            'title' => '有钱人聚会 女生 (' . $youqianren[ 0 ][ 'total' ] . ')人',
            'data' => $youqianren[ 0 ][ 'json' ],
            'div_id' => 'div_youqianren_female'
         ],
         [
            'title' => '有钱人聚会 男生 (' . $youqianren[ 1 ][ 'total' ] . ')人',
            'data' => $youqianren[ 1 ][ 'json' ],
            'div_id' => 'div_youqianren_male'
         ],
      ];

      $yincha = $this->_getAgeStatJSON( 1313941540, 1315549125 );
      $stat[] = [
         [
            'title' => '得闲饮茶聚会 女生 (' . $yincha[ 0 ][ 'total' ] . ')人',
            'data' => $yincha[ 0 ][ 'json' ],
            'div_id' => 'div_yincha_female'
         ],
         [
            'title' => '得闲饮茶聚会 男生 (' . $yincha[ 1 ][ 'total' ] . ')人',
            'data' => $yincha[ 1 ][ 'json' ],
            'div_id' => 'div_yincha_male'
         ],
      ];

      $qixi = $this->_getAgeStatJSON( 1312350024, 1313607290 );
      $stat[] = [
         [
            'title' => '七夕聚会 女生 (' . $qixi[ 0 ][ 'total' ] . ')人',
            'data' => $qixi[ 0 ][ 'json' ],
            'div_id' => 'div_qixi_female'
         ],
         [
            'title' => '七夕聚会 男生 (' . $qixi[ 1 ][ 'total' ] . ')人',
            'data' => $qixi[ 1 ][ 'json' ],
            'div_id' => 'div_qixi_male'
         ],
      ];

      echo new Template( 'FFchart', [ 'stat' => $stat ] );
      exit;
   }

}

//__END_OF_FILE__