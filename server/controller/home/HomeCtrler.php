<?php

namespace site\controller\home;

use site\controller\Home;
use lzx\html\HTMLElement;
use lzx\html\Template;
use site\dbobject\Node;
use site\dbobject\Activity;
use site\dbobject\Image;
use site\PageCache;
use site\SegmentCache;

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
         'recentActivities' => $this->_getRecentActivities(),
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
         $node = new Node();
         $arr = $node->getLatestForumTopics( 15 );

         $rightTagKey = 'rightTag';
         foreach ( $arr as $i => $n )
         {
            $arr[ $i ][ $rightTagKey ] = \date( 'H:i', $n[ 'create_time' ] );
            $arr[ $i ][ 'uri' ] = '/node/' . $n[ 'nid' ];
            $arr[ $i ][ 'title' ] = $this->html->truncate( $n[ 'title' ], 34 );
         }
         $ul = $this->_linkNodeList( $arr, $ulCache, $rightTagKey );
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
         $node = new Node();
         $arr = $node->getHotForumTopics( 15, $this->request->timestamp - 604800 );

         $rightTagKey = 'rightTag';
         foreach ( $arr as $i => $n )
         {
            $arr[ $i ][ $rightTagKey ] = $n[ 'comment_count' ];
            $arr[ $i ][ 'uri' ] = '/node/' . $n[ 'nid' ];
            $arr[ $i ][ 'title' ] = $this->html->truncate( $n[ 'title' ], 36 );
         }
         $ul = $this->_linkNodeList( $arr, $ulCache, $rightTagKey );
      }


      return $ul;
   }

   private function _getLatestYellowPages()
   {
      $ulCache = $this->cache->getSegment( 'latestYellowPages' );
      $ul = $ulCache->fetch();
      if ( !$ul )
      {
         $node = new Node();
         $arr = $node->getLatestYellowPages( 25 );

         $rightTagKey = 'rightTag';
         foreach ( $arr as $i => $n )
         {
            $arr[ $i ][ $rightTagKey ] = \date( 'm/d', $n[ 'exp_time' ] );
            $arr[ $i ][ 'uri' ] = '/node/' . $n[ 'nid' ];
            $arr[ $i ][ 'title' ] = $this->html->truncate( $n[ 'title' ], 34 );
         }
         $ul = $this->_linkNodeList( $arr, $ulCache, $rightTagKey );
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
         $node = new Node();
         $arr = $node->getLatestImmigrationPosts( 25 );

         $rightTagKey = 'rightTag';
         foreach ( $arr as $i => $n )
         {
            $arr[ $i ][ $rightTagKey ] = \date( 'm/d', $n[ 'create_time' ] );
            $arr[ $i ][ 'uri' ] = '/node/' . $n[ 'nid' ];
            $arr[ $i ][ 'title' ] = $this->html->truncate( $n[ 'title' ], 34 );
         }
         $ul = $this->_linkNodeList( $arr, $ulCache, $rightTagKey );
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
         $node = new Node();
         $arr = $node->getLatestForumTopicReplies( 15 );

         $rightTagKey = 'rightTag';
         foreach ( $arr as $i => $n )
         {
            $arr[ $i ][ $rightTagKey ] = $n[ 'comment_count' ];
            $arr[ $i ][ 'uri' ] = '/node/' . $n[ 'nid' ] . '?page=last#comment' . $n[ 'last_cid' ];
            $arr[ $i ][ 'title' ] = $this->html->truncate( $n[ 'title' ], 36 );
         }
         $ul = $this->_linkNodeList( $arr, $ulCache, $rightTagKey );
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
         $node = new Node();
         $arr = $node->getLatestYellowPageReplies( 25 );

         $rightTagKey = 'rightTag';
         foreach ( $arr as $i => $n )
         {
            $arr[ $i ][ $rightTagKey ] = $n[ 'comment_count' ];
            $arr[ $i ][ 'uri' ] = '/node/' . $n[ 'nid' ] . '?page=last#comment' . $n[ 'last_cid' ];
            $arr[ $i ][ 'title' ] = $this->html->truncate( $n[ 'title' ], 36 );
         }
         $ul = $this->_linkNodeList( $arr, $ulCache, $rightTagKey );
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
         $activity = new Activity();
         $arr = $activity->getRecentActivities( 12, $this->request->timestamp );

         foreach ( $arr as $i => $n )
         {
            $arr[ $i ][ 'uri' ] = '/node/' . $n[ 'nid' ];
            $arr[ $i ][ 'title' ] = '<span class="activity_' . $n[ 'class' ] . '">[' . date( 'm/d', $n[ 'start_time' ] ) . ']</span> ' . $this->html->truncate( $n[ 'title' ], 32 );
         }
         $ul = $this->_linkNodeList( $arr, $ulCache );
      }

      return $ul;
   }

   private function _linkNodeList( $arr, SegmentCache $ulCache, $rightTagKey = NULL )
   {
      $links = [ ];
      if ( $rightTagKey )
      {
         foreach ( $arr as $n )
         {
            $rightTag = new HTMLElement( 'span', $n[ $rightTagKey ], [ 'class' => "li_right" ] );
            $links[] = $rightTag . $this->html->link( $n[ 'title' ], $n[ 'uri' ] );
         }
      }
      else
      {
         foreach ( $arr as $n )
         {
            $links[] = $this->html->link( $n[ 'title' ], $n[ 'uri' ] );
         }
      }

      $ul = (string) $this->html->ulist( $links );

      $ulCache->store( $ul );
      foreach ( $arr as $n )
      {
         $ulCache->addParent( '/node/' . $n[ 'nid' ] );
      }

      return $ul;
   }

}

//__END_OF_FILE__
