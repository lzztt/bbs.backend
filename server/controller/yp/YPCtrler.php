<?php

namespace site\controller\yp;

use site\controller\YP;
use lzx\html\Template;
use site\dbobject\Tag;
use site\dbobject\Node;
use site\PageCache;

class YPCtrler extends YP
{

   public function run()
   {
      $this->cache = new PageCache( $this->request->uri );
      
      if ( !$this->id )
      {
         $this->_ypHome();
      }
      else
      {
         $this->_nodeList( $this->id );
      }
   }

// $yp, $groups, $boards are arrays of category id
   protected function _ypHome()
   {
      $tag = new Tag( Tag::YP_ID, NULL );
      $yp = $tag->getTagTree();
      $this->html->var[ 'content' ] = new Template( 'yp_home', ['tid' => $tag->id, 'yp' => $yp ] );
   }

   protected function _nodeList( $tid )
   {
      $tag = new Tag( $tid, NULL );
      $tagRoot = $tag->getTagRoot();
      $tids = \implode( ',', $tag->getLeafTIDs() );

      $breadcrumb = [ ];
      foreach ( $tagRoot as $i => $t )
      {
         $breadcrumb[] = [
            'href' => ($i === Tag::YP_ID ? '/yp' : ('/yp/' . $i)),
            'title' => $t[ 'description' ],
            'name' => $t[ 'name' ]
         ];
      }

      $node = new Node();
      $nodeCount = $node->getNodeCount( $tids );
      list($pageNo, $pageCount) = $this->_getPagerInfo( $node->getNodeCount( $tid ), self::NODES_PER_PAGE );
      $pager = $this->html->pager( $pageNo, $pageCount, '/yp/' . $tid );

      $nodes = $node->getYellowPageNodeList( $tids, self::NODES_PER_PAGE, ($pageNo - 1) * self::NODES_PER_PAGE );

      $nids = [ ];
      foreach ( $nodes as $i => $n )
      {
         $nids[] = $n[ 'id' ];
         $nodes[ $i ][ 'title' ] = $this->html->truncate( $n[ 'title' ], 45 );
      }

      $contents = [
         'tid' => $tid,
         'cateName' => $tag->name,
         'cateDescription' => $tag->description,
         'breadcrumb' => $this->html->breadcrumb( $breadcrumb ),
         'pager' => $pager,
         'nodes' => (empty( $nodes ) ? NULL : $nodes),
         'ajaxURI' => '/yp/ajax/viewcount?type=json&tid=' . $tid . '&nids=' . \implode( '_', $nids ) . '&nosession',
      ];
      $this->html->var[ 'content' ] = new Template( 'yp_list', $contents );
   }

}

//__END_OF_FILE__