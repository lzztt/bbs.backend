<?php

namespace site\controller\yp;

use site\controller\YP;
use lzx\html\Template;
use site\dbobject\Tag;
use site\dbobject\Node;
use site\dbobject\NodeYellowPage;
use site\dbobject\Image;

class NodeCtrler extends YP
{

   public function run()
   {
      if ( $this->request->uid != 1 )
      {
         $this->pageForbidden();
      }

      $tid = $this->id;
      if ( $tid <= 0 )
      {
         $this->pageNotFound();
      }

      $tag = new Tag();
      $tag->parent = $tid;
      if ( $tag->getCount() > 0 )
      {
         $this->error( '错误：您不能在该类别中添加黄页，请到它的子类别中添加。' );
      }

      if ( empty( $this->request->post ) )
      {
         $this->html->var[ 'content' ] = new Template( 'editor_bbcode_yp' );
      }
      else
      {
         $node = new Node();
         $node->tid = $tid;
         $node->uid = $this->request->uid;
         $node->title = $this->request->post[ 'title' ];
         $node->body = $this->request->post[ 'body' ];
         $node->createTime = $this->request->timestamp;
         $node->status = 1;
         $node->add();

         $nodeYP = new NodeYellowPage();
         $nodeYP->nid = $node->id;
         foreach ( \array_diff( $nodeYP->getProperties(), ['nid' ] ) as $key )
         {
            $nodeYP->$key = $this->request->post[ $key ];
         }
         $nodeYP->add();

         if ( isset( $this->request->post[ 'files' ] ) )
         {
            $file = new Image();
            $file->updateFileList( $this->request->post[ 'files' ], $this->config->path[ 'file' ], $node->id );
         }

         foreach ( ['latestYellowPages', '/yp/' . $tid ] as $key )
         {
            $this->_getIndependentCache( $key )->delete();
         }

         $this->redirect = '/node/' . $node->id;
      }
   }

}

//__END_OF_FILE__