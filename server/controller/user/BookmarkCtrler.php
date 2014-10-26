<?php

namespace site\controller\user;

use site\controller\User;
use site\dbobject\User as UserObject;
use lzx\html\Template;

class BookmarkCtrler extends User
{

   public function run()
   {
      if ( $this->request->uid == self::UID_GUEST )
      {
         $this->_displayLogin( $this->request->uri );
         return;
      }

      if ( $this->id && $this->id != $this->request->uid )
      {
         $this->pageForbidden();
      }

      if ( $this->args && $this->args[ 0 ] == 'delete' )
      {
         $this->_delete();
      }
      else
      {
         $this->_list();
      }
   }

   private function _list()
   {
      $nodePerPage = 50;
      $u = new UserObject( $this->request->uid, NULL );

      list($pageNo, $pageCount) = $this->_getPagerInfo( $u->countBookmark(), $nodePerPage );
      $pager = Template::pager( $pageNo, $pageCount, '/user/' . $u->id . '/bookmark' );

      $nodes = $u->listBookmark( $nodePerPage, ($pageNo - 1) * $nodePerPage );
      $this->_var[ 'content' ] = new Template( 'bookmark_list', ['nodes' => $nodes, 'pager' => $pager, 'userLinks' => $this->_getUserLinks( '/user/' . $u->id . '/bookmark' ) ] );
   }

   private function _delete()
   {
      $u = new UserObject( $this->request->uid, NULL );
      if ( $this->request->get[ 'nid' ] )
      {
         $nids = \explode( ',', $this->request->get[ 'nid' ] );
         foreach ( $nids as $nid )
         {
            $u->deleteBookmark( $nid );
         }
      }
      $this->response->setContent( NULL );
   }

}

//__END_OF_FILE__
