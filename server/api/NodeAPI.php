<?php

namespace site\api;

use site\Service;
use site\dbobject\Node;
use site\dbobject\Comment;

class NodeAPI extends Service
{

   const COMMENT_PER_PAGE = 20;

   /**
    * get nodes for a user
    * uri: /api/node/<nid>
    *      /api/node/<nid>?p=<pageNo>
    */
   public function get()
   {
      if ( empty( $this->args ) || !\is_numeric( $this->args[ 0 ] ) )
      {
         $this->forbidden();
      }

      $nid = (int) $this->args[ 0 ];

      $n = new Node( $nid, 'id,title,body' );
      $data = $n->toArray();

      // get Comments
      $comment = new Comment();
      $comment->nid = $nid;
      $commentCount = $comment->getCount();
      if ( $commentCount == 0 )
      {
         $data[ 'pageNo' ] = 1;
         $data[ 'pageCount' ] = 1;
         $data[ 'comments' ] = [ ];
      }
      else
      {
         list($pageNo, $pageCount) = $this->_getPagerInfo( $commentCount, self::COMMENT_PER_PAGE );
         $data[ 'pageNo' ] = $pageNo;
         $data[ 'pageCount' ] = $pageCount;

         $data[ 'comments' ] = $comment->getList( 'body', self::COMMENT_PER_PAGE, ($pageNo - 1) * self::COMMENT_PER_PAGE );
      }

      $this->_json( $data );
   }

   /**
    * add a node to user's node list
    * uri: /api/node[?action=post]
    * post: nid=<nid>
    */
   public function post()
   {
      if ( !$this->request->uid || empty( $this->request->post ) )
      {
         $this->forbidden();
      }

      $nid = (int) $this->request->post[ 'nid' ];
      if ( $nid <= 0 )
      {
         $this->error( 'node does not exist' );
      }

      $u = new User( $this->request->uid, NULL );

      $u->addBookmark( $nid );

      $this->_json( NULL );
   }

   /**
    * remove one node or multiple modes from user's node list
    * uri: /api/node/<nid>(,<nid>,...)?action=delete
    */
   public function delete()
   {
      if ( !$this->request->uid || empty( $this->args ) )
      {
         $this->forbidden();
      }

      $nids = [ ];

      foreach ( \explode( ',', $this->args[ 0 ] ) as $nid )
      {
         if ( \is_numeric( $nid ) && \intval( $nid ) > 0 )
         {
            $nids[] = (int) $nid;
         }
      }

      $u = new User( $this->request->uid, NULL );
      foreach ( $nids as $nid )
      {
         $u->deleteBookmark( $nid );
      }

      $this->_json( NULL );
   }

}

//__END_OF_FILE__
