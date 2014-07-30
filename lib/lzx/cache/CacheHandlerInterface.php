<?php

namespace lzx\cache;

use lzx\cache\Cache;

interface CacheHandlerInterface
{

   /**
    * Factory design patern
    * @return \lzx\cache\Cache
    */
   public function createCache( $name );

   public function getCleanName( $name );

   public function getFileName( Cache $cache );

   public function getID( $name );

   public function unlinkParents( $id );

   public function linkParents( $id, array $parents );

   public function linkChildren( $id, array $children );

   public function getChildren( $id );
}

//__END_OF_FILE__
