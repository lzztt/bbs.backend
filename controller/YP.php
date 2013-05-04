<?php

namespace site\controller;

use lzx\core\Controller;
use lzx\html\Template;
use site\dataobject\Tag;
use site\dataobject\Node;
use site\dataobject\NodeYellowPage;
use site\dataobject\File;

class YP extends Controller
{

   const YP_ROOT_TID = 2;
   const NODES_PER_PAGE = 25;

   public function run()
   {
      $this->checkAJAX();

      $page = $this->loadController('Page');
      $page->updateInfo();
      $this->checkAJAX();
      $page->setPage();

      if (is_null($this->request->args[1]))
      {
         $this->showYellowPageHome();
      }
      elseif (is_numeric($this->request->args[1]))
      {
         $tid = (int) $this->request->args[1];
         if ($this->request->args[2] == 'node')
         {
            $this->createYellowPageNode($tid);
         }
         else
         {
            $this->showYellowPageList($tid);
         }
      }
      else
      {
         if ($this->request->args[1] == 'join')
         {
            $this->showYellowPageJoin();
         }
         else
         {
            $this->request->pageNotFound();
         }
      }
   }

   public function showYellowPageJoin()
   {
      $this->html->var['content'] = new Template('yp_join');
   }

// $yp, $groups, $boards are arrays of category id
   public function showYellowPageHome()
   {
      $tag = new Tag();
      $tag->tid = self::YP_ROOT_TID;
      $yp = $tag->getTagTree();
      $this->html->var['content'] = new Template('yp_home', array('yp' => $yp));
   }

   public function showYellowPageList($tid)
   {
      $tag = new Tag($tid, 'tid,name,parent');
      $tids = array($tid);
      foreach ($tag->getChildren('tid') as $child)
      {
         $tids[] = $child['tid'];
      }

      $node = new Node();
      $nodeCount = $node->getNodeCount($tids);

      if ($tag->parent != self::YP_ROOT_TID)
      {
         $parent = new Tag($tag->parent, 'tid,name,parent');
         if ($parent->parent != self::YP_ROOT_TID)
         {
            $this->request->pageNotFound();
         }
         $breadcrumb = '<a href="/yp">黄页</a> > <a href="/yp/' . $parent->tid . '">' . $parent->name . '</a>';
         $this->cache->storeMap('/yp/' . $tid, '/yp/' . $tag->parent);
      }
      else
      {
         $breadcrumb = '<a href="/yp">黄页</a>';
      }

      $pageNo = $this->request->get['page'];
      $pageCount = \ceil($nodeCount / self::NODES_PER_PAGE);
      list($pageNo, $pager) = $this->html->generatePager($pageNo, $pageCount, '/yp/' . $tid);

      $node = new Node();
      $nodes = $node->getYellowPageNodeList($tids, self::NODES_PER_PAGE, ($pageNo - 1) * self::NODES_PER_PAGE);

      $nids = array();
      foreach ($nodes as $i => $n)
      {
         $nids[] = $n['nid'];
         $nodes[$i]['title'] = $this->html->truncate($n['title'], 45);
      }

      $contents = array(
         'tid' => $tid,
         'cateName' => $tags[$tid]['name'],
         'cateDescription' => $tags[$tid]['description'],
         'breadcrumb' => $breadcrumb,
         'pager' => $pager,
         'nodes' => $nodes,
         'ajaxURI' => '/yp/ajax/viewcount?type=json&tid=' . $tid . '&nids=' . \implode('_', $nids),
      );
      $this->html->var['content'] = new Template('yp_list', $contents);
   }

   public function ajax()
   {
      // url = /forum/ajax/viewcount?tid=<tid>&nids=<nid>_<nid>_

      $viewCount = array();
      if ($this->request->args[2] == 'viewcount')
      {
         //$tid = \intval($this->request->get['tid']);
         $nids = explode('_', $this->request->get['nids']);
         foreach ($nids as $i => $nid)
         {
            $nids[$i] = (int) $nid;
         }
         if (\sizeof($nids) > 0)
         {
            $node = new Node();
            //$node->tid = $tid;
            $node->where('nid', $nids, '=');
            $arr = $node->getList('nid,viewCount');

            foreach ($arr as $r)
            {
               $viewCount['viewCount_' . $r['nid']] = (int) $r['viewCount'];
            }
         }
      }

      return $viewCount;
   }

   public function createYellowPageNode($tid)
   {
      if ($this->request->uid != 1)
      {
         $this->request->pageForbidden();
      }

      $tag = new Tag();
      $tag->parent = $tid;
      if ($tag->getCount() > 0)
      {
         $this->error('错误：您不能在该类别中添加黄页，请到它的子类别中添加。');
      }

      if (empty($this->request->post))
      {
         $this->html->var['content'] = new Template('editor_bbcode_yp');
      }
      else
      {
         $node = new Node();
         $node->tid = $tid;
         $node->uid = $this->request->uid;
         $node->title = $this->request->post['title'];
         $keys = array('address', 'phone', 'email', 'website', 'fax', 'introduction');
         $node->body = $this->request->post['introduction'];
         $node->createTime = $this->request->timestamp;
         $node->status = 1;
         $node->save();

         $nodeYP = new NodeYellowPage();
         $nodeYP->nid = $node->nid;
         foreach (array_diff($nodeYP->getFields(), array('nid')) as $key)
         {
            $nodeYP->$key = $this->request->post[$key];
         }
         $nodeYP->add();

         if (isset($this->request->post['files']))
         {
            $file = new File();
            $file->updateFileList($this->request->post['files'], $node->nid);
         }

         $this->cache->delete('/yp/' . $tid);
         $this->cache->delete('latestYellowPages');

         $this->request->redirect('/node/' . $node->nid);
      }
   }

}

//__END_OF_FILE__