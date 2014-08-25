<?php

namespace site\controller\home;

use site\controller\Home;
use lzx\html\Template;
use site\dbobject\Node;
use site\dbobject\Activity;
use site\dbobject\Image;
use lzx\cache\PageCache;
use lzx\cache\SegmentCache;
use site\dbobject\Tag;

class HomeCtrler extends Home
{

   public function run()
   {
      $this->cache = new PageCache( $this->request->uri );

      $func = '_' . $this->site . 'Home';
      if ( \method_exists( $this, $func ) )
      {
         $this->$func();
      }
      else
      {
         $this->error( 'unsupported site: ' . $this->site );
      }
   }

   private function _houstonHome()
   {
      $content = [
         'recentActivities' => $this->_getRecentActivities(),
         'latestForumTopics' => $this->_getLatestForumTopics(),
         'hotForumTopics' => $this->_getHotForumTopics(),
         'latestYellowPages' => $this->_getLatestYellowPages(),
         'latestImmigrationPosts' => $this->_getLatestImmigrationPosts(),
         'latestForumTopicReplies' => $this->_getLatestForumTopicReplies(),
         'latestYellowPageReplies' => $this->_getLatestYellowPageReplies(),
         'imageSlider' => $this->_getImageSlider(),
      ];
      $this->html->var[ 'content' ] = new Template( 'home', $content, $this->site );
   }

   // BEGIN DALLAS HOME
   private function _dallasHome()
   {
      $tag = new Tag( $this->_forumRootID[ $this->site ], NULL );
      $tagTree = $tag->getTagTree();

      $nodeInfo = [ ];
      $groupTrees = [ ];
      foreach ( $tagTree[ $tag->id ][ 'children' ] as $group_id )
      {
         $groupTrees[ $group_id ] = [ ];
         $group = $tagTree[ $group_id ];
         $groupTrees[ $group_id ][ $group_id ] = $group;
         foreach ( $group[ 'children' ] as $board_id )
         {
            $groupTrees[ $group_id ][ $board_id ] = $tagTree[ $board_id ];
            $nodeInfo[ $board_id ] = $this->_nodeInfo( $board_id );
            $this->cache->addParent( '/forum/' . $board_id );
         }
      }

      $content = [
         'latestForumTopics' => $this->_getLatestForumTopics(),
         'hotForumTopics' => $this->_getHotForumTopics(),
         'latestForumTopicReplies' => $this->_getLatestForumTopicReplies(),
         'imageSlider' => $this->_getImageSlider(),
         'groups' => $groupTrees,
         'nodeInfo' => $nodeInfo
      ];

      $this->html->var[ 'content' ] = new Template( 'home', $content, $this->site );
   }

   protected function _nodeInfo( $tid )
   {
      $tag = new Tag( $tid, NULL );

      foreach ( $tag->getNodeInfo( $tid ) as $v )
      {
         $v[ 'create_time' ] = \date( 'm/d/Y H:i', $v[ 'create_time' ] );
         if ( $v[ 'cid' ] == 0 )
         {
            $node = $v;
         }
         else
         {
            $comment = $v;
         }
      }
      return [ 'node' => $node, 'comment' => $comment ];
   }

   // END DALLAS HOME

   private function _getImageSlider()
   {
      $ulCache = $this->cache->getSegment( 'imageSlider' );
      $ul = $ulCache->fetch();
      if ( !$ul )
      {
         $img = new Image();
         $images = $img->getRecentImages();
         \shuffle( $images );

         $content[ 'images' ] = $images;
         $ul = new Template( 'image_slider', $content );

         $ulCache->store( $ul );

         foreach ( $images as $i )
         {
            $ulCache->addParent( '/node/' . $i[ 'nid' ] );
         }
         $this->_getCacheEvent( 'ImageUpdate' )->addListener( $ulCache );
      }

      return $ul;
   }

   private function _getLatestForumTopics()
   {
      $ulCache = $this->cache->getSegment( 'latestForumTopics' );
      $ul = $ulCache->fetch();
      if ( !$ul )
      {
         $arr = [ ];

         foreach ( (new Node() )->getLatestForumTopics( $this->_forumRootID[ $this->site ], 15 ) as $n )
         {
            $arr[] = [ 'after' => \date( 'H:i', $n[ 'create_time' ] ),
               'uri' => '/node/' . $n[ 'nid' ],
               'text' => $n[ 'title' ] ];
         }
         $ul = $this->_linkNodeList( $arr, $ulCache );
      }
      $this->_getCacheEvent( 'ForumNode' )->addListener( $ulCache );

      return $ul;
   }

   private function _getHotForumTopics()
   {
      $ulCache = $this->cache->getSegment( 'hotForumTopics' );
      $ul = $ulCache->fetch();
      if ( !$ul )
      {
         $arr = [ ];

         foreach ( (new Node() )->getHotForumTopics( $this->_forumRootID[ $this->site ], 15, $this->request->timestamp - 604800 ) as $n )
         {
            $arr[] = [ 'after' => $n[ 'comment_count' ],
               'uri' => '/node/' . $n[ 'nid' ],
               'text' => $n[ 'title' ] ];
         }
         $ul = $this->_linkNodeList( $arr, $ulCache );
      }


      return $ul;
   }

   private function _getLatestYellowPages()
   {
      $ulCache = $this->cache->getSegment( 'latestYellowPages' );
      $ul = $ulCache->fetch();
      if ( !$ul )
      {
         $arr = [ ];

         foreach ( (new Node() )->getLatestYellowPages( $this->_ypRootID[ $this->site ], 15 ) as $n )
         {
            $arr[] = [ 'after' => \date( 'm/d', $n[ 'exp_time' ] ),
               'uri' => '/node/' . $n[ 'nid' ],
               'text' => $n[ 'title' ] ];
         }
         $ul = $this->_linkNodeList( $arr, $ulCache );
      }
      $this->_getCacheEvent( 'YellowPageNode' )->addListener( $ulCache );

      return $ul;
   }

   private function _getLatestImmigrationPosts()
   {
      $ulCache = $this->cache->getSegment( 'latestImmigrationPosts' );
      $ul = $ulCache->fetch();
      if ( !$ul )
      {
         $arr = [ ];

         foreach ( (new Node() )->getLatestImmigrationPosts( 15 ) as $n )
         {
            $arr[] = [ 'after' => \date( 'm/d', $n[ 'create_time' ] ),
               'uri' => '/node/' . $n[ 'nid' ],
               'text' => $n[ 'title' ] ];
         }
         $ul = $this->_linkNodeList( $arr, $ulCache );
      }
      $this->_getCacheEvent( 'ImmigrationNode' )->addListener( $ulCache );

      return $ul;
   }

   private function _getLatestForumTopicReplies()
   {
      $ulCache = $this->cache->getSegment( 'latestForumTopicReplies' );
      $ul = $ulCache->fetch();
      if ( !$ul )
      {
         $arr = [ ];

         foreach ( (new Node() )->getLatestForumTopicReplies( $this->_forumRootID[ $this->site ], 15 ) as $n )
         {
            $arr[] = [ 'after' => $n[ 'comment_count' ],
               'uri' => '/node/' . $n[ 'nid' ] . '?page=last#comment' . $n[ 'last_cid' ],
               'text' => $n[ 'title' ] ];
         }
         $ul = $this->_linkNodeList( $arr, $ulCache );
      }
      $this->_getCacheEvent( 'ForumComment' )->addListener( $ulCache );

      return $ul;
   }

   private function _getLatestYellowPageReplies()
   {
      $ulCache = $this->cache->getSegment( 'latestYellowPageReplies' );
      $ul = $ulCache->fetch();
      if ( !$ul )
      {
         $arr = [ ];

         foreach ( (new Node() )->getLatestYellowPageReplies( $this->_ypRootID[ $this->site ], 15 ) as $n )
         {
            $arr[] = [ 'after' => $n[ 'comment_count' ],
               'uri' => '/node/' . $n[ 'nid' ] . '?page=last#comment' . $n[ 'last_cid' ],
               'text' => $n[ 'title' ] ];
         }
         $ul = $this->_linkNodeList( $arr, $ulCache );
      }
      $this->_getCacheEvent( 'YellowPageComment' )->addListener( $ulCache );

      return $ul;
   }

   private function _getRecentActivities()
   {
      $ulCache = $this->cache->getSegment( 'recentActivities' );
      $ul = $ulCache->fetch();
      if ( !$ul )
      {
         $arr = [ ];

         foreach ( (new Activity() )->getRecentActivities( 12, $this->request->timestamp ) as $n )
         {
            $arr[] = [ 'class' => 'activity_' . $n[ 'class' ],
               'after' => \date( 'm/d', $n[ 'start_time' ] ),
               'uri' => '/node/' . $n[ 'nid' ],
               'text' => $n[ 'title' ] ];
         }
         $ul = $this->_linkNodeList( $arr, $ulCache );
      }

      return $ul;
   }

   private function _linkNodeList( array $arr, SegmentCache $ulCache )
   {
      $ul = (string) new Template( 'home_itemlist', ['data' => $arr ] );

      $ulCache->store( $ul );
      foreach ( $arr as $n )
      {
         $ulCache->addParent( \strtok( $n[ 'uri' ], '?#' ) );
      }

      return $ul;
   }

}

//__END_OF_FILE__
