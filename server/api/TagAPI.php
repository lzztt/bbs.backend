<?php

namespace site\api;

use site\Service;
use site\dbobject\Tag;

class TagAPI extends Service
{

   const NODE_PER_PAGE = 20;

   /**
    * get tags for a user
    * uri: /api/tag/<tid>
    *      /api/tag/<tid>?p=<pageNo>
    *      /api/tag/<tid>?n=<nodeId>
    */
   public function get()
   {
      if ( empty( $this->args ) || !\is_numeric( $this->args[ 0 ] ) )
      {
         $this->forbidden();
      }

      $tid = (int) $this->args[ 0 ];

      $t = new Tag( $tid, NULL );

      $nodes = $t->getNodeList( 1, self::NODE_PER_PAGE, 0 );

      $this->_json( $nodes );
   }

}

//__END_OF_FILE__
