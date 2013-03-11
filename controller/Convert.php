<?php

namespace site\controller;

use lzx\core\Controller;
use site\dataobject\Tag;
use site\dataobject\Node;
use site\dataobject\NodeYellowPage;

/**
 * Description of convert
 *
 * @author ikki
 */
class Convert extends Controller
{

   public function run()
   {
      $this->request->pageNotFound();

      $t = new Tag();
      echo '<pre>';
      $t->importTag();
      echo '</pre>';
      $t->buildNodeTagMap();

      $this->yp();
   }

   public function yp()
   {
      $tags = Tag::getLeafTags(2);
      $tids = array();
      foreach ($tags as $t)
      {
         $tids[] = $t['tid'];
      }

      $node = new Node();
      $node->where('tid', $tids, '=');
      $nodes = $node->getList();
      foreach ($nodes as $nd)
      {
         $arr = \unserialize(\base64_decode($nd['body']));
         $n_yp = new NodeYelloWPage();
         $n_yp->nid = $nd['nid'];
         $n_yp->address = $arr['address'];
         $n_yp->phone = $arr['phone'];
         $n_yp->fax = $arr['fax'];
         $n_yp->email = $arr['email'];
         $n_yp->website = $arr['website'];
         $n_yp->add();

         $n = new Node();
         $n->nid = $nd['nid'];
         $n->body = $arr['introduction'];
         if ($n->body)
         {
            $n->update('body');
         }
         else
         {
            $n->setNULL('body');
         }
         echo 'updated ' . $n->nid;
      }
   }

}

?>
