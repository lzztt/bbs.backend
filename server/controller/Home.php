<?php

namespace site\controller;

use site\Controller;
use lzx\html\HTMLElement;
use lzx\html\Template;
use site\dbobject\Node;
use site\dbobject\Activity;
use site\dbobject\Image;
use site\dbobject\User;

class Home extends Controller
{

   public function run()
   {
      parent::run();
      $this->checkAJAX();

      $content = array(
         'recentActivities' => $this->getRecentActivities(),
         'latestForumTopics' => $this->getLatestForumTopics(),
         'hotForumTopics' => $this->getHotForumTopics(),
         'latestYellowPages' => $this->getLatestYellowPages(),
         'latestImmigrationPosts' => $this->getLatestImmigrationPosts(),
         'latestForumTopicReplies' => $this->getLatestForumTopicReplies(),
         'latestYellowPageReplies' => $this->getLatestYellowPageReplies(),
         'recentActivities' => $this->getRecentActivities(),
      );

      $content += array(
         'imageSlider' => $this->getImageSlider(),
      );

      $this->html->var['content'] = new Template( 'home', $content );
   }

   public function ajax()
   {
      $node = new Node();
      $r = $node->getNodeStat();

      $r['alexa'] = \strval( new Template( 'alexa' ) );


      $user = new User();
      $res = \array_merge( $r, $user->getUserStat( $this->request->timestamp - 300 ) );

      return $res;
   }

   public function getImageSlider()
   {
      $cache_key = 'imageSlider';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
      {
         // try 3 times 10 images first
         $img = new Image();
         $images = $img->getRecentImages();
         \shuffle( $images );

         $content['images'] = $images;
         $ul = new Template( 'image_slider', $content );

         $this->cache->store( $cache_key, $ul );
         foreach ( $images as $i )
         {
            $this->cache->storeMap( '/node/' . $i['nid'], $cache_key );
         }
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   public function getLatestForumTopics()
   {
      $cache_key = 'latestForumTopics';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
      {
         $node = new Node();
         $arr = $node->getLatestForumTopics();

         $rightTagKey = 'rightTag';
         foreach ( $arr as $i => $n )
         {
            $arr[$i][$rightTagKey] = \date( 'H:i', $n['createTime'] );
            $arr[$i]['uri'] = '/node/' . $n['nid'];
            $arr[$i]['title'] = $this->html->truncate( $n['title'], 34 );
         }
         $ul = $this->_linkNodeList( $arr, $cache_key, $rightTagKey );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   public function getHotForumTopics()
   {
      $cache_key = 'hotForumTopics';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
      {
         $node = new Node();
         $arr = $node->getHotForumTopics( $this->request->timestamp - 604800 );

         $rightTagKey = 'rightTag';
         foreach ( $arr as $i => $n )
         {
            $arr[$i][$rightTagKey] = $n['commentCount'];
            $arr[$i]['uri'] = '/node/' . $n['nid'];
            $arr[$i]['title'] = $this->html->truncate( $n['title'], 36 );
         }
         $ul = $this->_linkNodeList( $arr, $cache_key, $rightTagKey );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   public function getLatestYellowPages()
   {
      $cache_key = 'latestYellowPages';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
      {
         $node = new Node();
         $arr = $node->getLatestYellowPages();

         $rightTagKey = 'rightTag';
         foreach ( $arr as $i => $n )
         {
            $arr[$i][$rightTagKey] = \date( 'm/d', $n['createTime'] );
            $arr[$i]['uri'] = '/node/' . $n['nid'];
            $arr[$i]['title'] = $this->html->truncate( $n['title'], 34 );
         }
         $ul = $this->_linkNodeList( $arr, $cache_key, $rightTagKey );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   public function getLatestImmigrationPosts()
   {
      $cache_key = 'latestImmigrationPosts';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
      {
         $node = new Node();
         $arr = $node->getLatestImmigrationPosts();

         $rightTagKey = 'rightTag';
         foreach ( $arr as $i => $n )
         {
            $arr[$i][$rightTagKey] = \date( 'm/d', $n['createTime'] );
            $arr[$i]['uri'] = '/node/' . $n['nid'];
            $arr[$i]['title'] = $this->html->truncate( $n['title'], 34 );
         }
         $ul = $this->_linkNodeList( $arr, $cache_key, $rightTagKey );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   public function getLatestForumTopicReplies()
   {
      $cache_key = 'latestForumTopicReplies';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
      {
         $node = new Node();
         $arr = $node->getLatestForumTopicReplies();

         $rightTagKey = 'rightTag';
         foreach ( $arr as $i => $n )
         {
            $arr[$i][$rightTagKey] = $n['commentCount'];
            $arr[$i]['uri'] = '/node/' . $n['nid'] . '?page=last#comment' . $n['lastCID'];
            $arr[$i]['title'] = $this->html->truncate( $n['title'], 36 );
         }
         $ul = $this->_linkNodeList( $arr, $cache_key, $rightTagKey );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   public function getLatestYellowPageReplies()
   {
      $cache_key = 'latestYellowPageReplies';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
      {
         $node = new Node();
         $arr = $node->getLatestYellowPageReplies();

         $rightTagKey = 'rightTag';
         foreach ( $arr as $i => $n )
         {
            $arr[$i][$rightTagKey] = $n['commentCount'];
            $arr[$i]['uri'] = '/node/' . $n['nid'] . '?page=last#comment' . $n['lastCID'];
            $arr[$i]['title'] = $this->html->truncate( $n['title'], 36 );
         }
         $ul = $this->_linkNodeList( $arr, $cache_key, $rightTagKey );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   public function getRecentActivities()
   {
      $cache_key = 'recentActivities';
      $ul = $this->cache->fetch( $cache_key );
      if ( $ul === FALSE )
      {
         $activity = new Activity();
         $arr = $activity->getRecentActivities( 12, $this->request->timestamp );

         foreach ( $arr as $i => $n )
         {
            $arr[$i]['uri'] = '/node/' . $n['nid'];
            $arr[$i]['title'] = '<span class="activity_' . $n['class'] . '">[' . date( 'm/d', $n['startTime'] ) . ']</span> ' . $this->html->truncate( $n['title'], 32 );
         }
         $ul = $this->_linkNodeList( $arr, $cache_key );
      }

      $this->cache->storeMap( $cache_key, '/' );
      return $ul;
   }

   private function _linkNodeList( $arr, $cache_key, $rightTagKey = NULL )
   {
      $links = array( );
      if ( $rightTagKey )
      {
         foreach ( $arr as $n )
         {
            $rightTag = new HTMLElement( 'span', $n[$rightTagKey], array( 'class' => "li_right" ) );
            $links[] = $rightTag . $this->html->link( $n['title'], $n['uri'] );
         }
      }
      else
      {
         foreach ( $arr as $n )
         {
            $links[] = $this->html->link( $n['title'], $n['uri'] );
         }
      }

      $ul = (string) $this->html->ulist( $links );

      $this->cache->store( $cache_key, $ul );
      foreach ( $arr as $n )
      {
         $this->cache->storeMap( '/node/' . $n['nid'], $cache_key );
      }

      return $ul;
   }

}

//__END_OF_FILE__
