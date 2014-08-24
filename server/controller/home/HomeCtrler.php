<?php

namespace site\controller\home;

use site\controller\Home;
use lzx\html\Template;
use site\dbobject\Node;
use site\dbobject\Activity;
use site\dbobject\Image;
use lzx\cache\PageCache;
use lzx\cache\SegmentCache;

class HomeCtrler extends Home
{

   public function run()
   {
      $this->cache = new PageCache( $this->request->uri );

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

      $this->html->var[ 'content' ] = new Template( 'home', $content );
   }

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

         foreach ( (new Node() )->getLatestForumTopics( 15 ) as $n )
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

         foreach ( (new Node() )->getHotForumTopics( 15, $this->request->timestamp - 604800 ) as $n )
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

         foreach ( (new Node() )->getLatestYellowPages( 15 ) as $n )
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

         foreach ( (new Node() )->getLatestForumTopicReplies( 15 ) as $n )
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

         foreach ( (new Node() )->getLatestYellowPageReplies( 15 ) as $n )
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
