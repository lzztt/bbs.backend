<?php

namespace site\controller\adm;

use site\controller\Adm;

class CacheCtrler extends Adm
{

   public function run()
   {
      $key = $this->request->post[ 'key' ];
      $form = '<div><form action="" method="post" accept-charset="UTF-8">Search Cache Key: <input type="text" value="' . $key . '" name="key" /><br /><input type="submit" value="Submit" /></form></div>';
      $this->_var[ 'content' ] = $form;

      // list file cache
      if ( $key )
      {
         $files = \glob( $this->cache->path . '/' . \preg_replace( '/[^0-9a-z\.\*\_\-]/i', '_', $key ) . '*' );

         if ( $files )
         {
            foreach ( $files as $f )
            {
               $f = \str_replace( $this->cache->path, '', $f );
               $li .= '<li><input type="checkbox" name="cache[]" value="' . $f . '"/>' . $f . '</li>';
            }

            $form = '<div><form action="" method="post" accept-charset="UTF-8"><ol>' . $li . '</ol><input type="submit" value="Delete" /></form></div>';
            $this->_var[ 'content' ] .= $form;
         }
         else
         {
            $this->_var[ 'content' ] .= 'No Cache File Found :(';
         }
      }
      // delete file cache
      if ( \sizeof( $this->request->post[ 'cache' ] ) > 0 )
      {
         foreach ( $this->request->post[ 'cache' ] as $f )
         {
            \unlink( $this->cache->path . $f );
         }

         $this->_var[ 'content' ] .= '<div>The following cache files have been deleted:<br />' . \implode( '<br />', $this->request->post[ 'cache' ] ) . '</div>';
      }
   }

}

//__END_OF_FILE__
