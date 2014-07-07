<?php

namespace site\controller\home;

use site\controller\Home;
use lzx\html\HTMLElement;
use lzx\html\Template;
use site\dbobject\Node;
use site\dbobject\Activity;
use site\dbobject\Image;

class HomeCtrler extends Home
{

   public function run()
   {
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
      $cache_key = 'imageSlider';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
      {
         $img = new Image();
         $images = $img->getRecentImages();
         \shuffle( $images );

         $content[ 'images' ] = $images;
         $ul = new Template( 'image_slider', $content );

         $this->cache->store( $cache_key, $ul );
         foreach ( $images as $i )
         {
            $this->cache->storeMap( '/node/' . $i[ 'nid' ], $cache_key );
         }
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   private function _getLatestForumTopics()
   {
      $cache_key = 'latestForumTopics';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
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
         $ul = $this->_linkNodeList( $arr, $cache_key, $rightTagKey );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   private function _getHotForumTopics()
   {
      $cache_key = 'hotForumTopics';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
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
         $ul = $this->_linkNodeList( $arr, $cache_key, $rightTagKey );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   private function _getLatestYellowPages()
   {
      $cache_key = 'latestYellowPages';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
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
         $ul = $this->_linkNodeList( $arr, $cache_key, $rightTagKey );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   private function _getLatestImmigrationPosts()
   {
      $cache_key = 'latestImmigrationPosts';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
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
         $ul = $this->_linkNodeList( $arr, $cache_key, $rightTagKey );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   private function _getLatestForumTopicReplies()
   {
      $cache_key = 'latestForumTopicReplies';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
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
         $ul = $this->_linkNodeList( $arr, $cache_key, $rightTagKey );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   private function _getLatestYellowPageReplies()
   {
      $cache_key = 'latestYellowPageReplies';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
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
         $ul = $this->_linkNodeList( $arr, $cache_key, $rightTagKey );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   private function _getRecentActivities()
   {
      $cache_key = 'recentActivities';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
      {
         $activity = new Activity();
         $arr = $activity->getRecentActivities( 12, $this->request->timestamp );

         foreach ( $arr as $i => $n )
         {
            $arr[ $i ][ 'uri' ] = '/node/' . $n[ 'nid' ];
            $arr[ $i ][ 'title' ] = '<span class="activity_' . $n[ 'class' ] . '">[' . date( 'm/d', $n[ 'start_time' ] ) . ']</span> ' . $this->html->truncate( $n[ 'title' ], 32 );
         }
         $ul = $this->_linkNodeList( $arr, $cache_key );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   private function _linkNodeList( $arr, $cache_key, $rightTagKey = NULL )
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

      $this->cache->store( $cache_key, $ul );
      foreach ( $arr as $n )
      {
         $this->cache->storeMap( '/node/' . $n[ 'nid' ], $cache_key );
      }

      return $ul;
   }

}

//__END_OF_FILE__
