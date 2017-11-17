<?php declare(strict_types=1);

namespace site\handler\node;

use Exception;
use site\handler\node\Node;
use lzx\core\BBCodeRE as BBCode;
use lzx\html\HTMLElement;
use lzx\html\Template;
use site\dbobject\Node as NodeObject;
use lzx\cache\PageCache;

class Handler extends Node
{
    public function run()
    {
        $this->cache = new PageCache($this->request->uri);

        list($nid, $type) = $this->getNodeType();
        $method = 'display' . $type;
        $this->$method($nid);

        $this->getCacheEvent('NodeUpdate', $nid)->addListener($this->cache);
    }

    private function displayForumTopic($nid)
    {
        $nodeObj = new NodeObject();
        $node = $nodeObj->getForumNode($nid);
        $tags = $nodeObj->getTags($nid);

        $this->var['head_title'] = $node['title'];
        $this->var['head_description'] = $node['title'];

        if (!$node) {
            $this->pageNotFound();
        }

        $breadcrumb = [];
        foreach ($tags as $i => $t) {
            $breadcrumb[$t['name']] = ($i === self::$city->tidForum ? '/forum' : ('/forum/' . $i));
        }
        $breadcrumb[$node['title']] = null;

        list($pageNo, $pageCount) = $this->getPagerInfo($node['comment_count'], self::COMMENTS_PER_PAGE);
        $pager = Template::pager($pageNo, $pageCount, '/node/' . $node['id']);

        $postNumStart = ($pageNo - 1) * self::COMMENTS_PER_PAGE; // first page start from the node and followed by comments

        $contents = [
            'nid' => $nid,
            'tid' => $node['tid'],
            'commentCount' => $node['comment_count'] - 1,
            'status' => $node['status'],
            'breadcrumb' => Template::breadcrumb($breadcrumb),
            'pager' => $pager,
            'postNumStart' => $postNumStart,
            'ajaxURI' => '/api/viewcount/' . $nid
        ];

        $posts = [];

        $authorPanelInfo = [
            'uid' => null,
            'username' => null,
            'avatar' => null,
            'sex' => null,
            'access_ip' => null,
            'join_time' => null,
            'points' => null,
        ];

        $nodeComment = ($pageNo == 1);
        $nodeObj = new NodeObject();
        $comments = $nodeObj->getForumNodeComments($nid, self::COMMENTS_PER_PAGE, ($pageNo - 1) * self::COMMENTS_PER_PAGE);

        if (sizeof($comments) > 0) {
            foreach ($comments as $c) {
                $c['type'] = 'comment';
                $c['createTime'] = date('m/d/Y H:i', (int) $c['create_time']);
                if ($c['lastModifiedTime']) {
                    $c['lastModifiedTime'] = date('m/d/Y H:i', (int) $c['last_modified_time']);
                }

                try {
                    $c['HTMLbody'] = BBCode::parse($c['body']);
                } catch (Exception $e) {
                    $c['HTMLbody'] = nl2br($c['body']);
                    $this->logger->error($e->getMessage(), $e->getTrace());
                }
                // $c['signature'] = nl2br($c['signature']);
                $c['authorPanel'] = $this->authorPanel(array_intersect_key($c, $authorPanelInfo));
                $c['city'] = $this->request->getCityFromIP($c['access_ip']);
                $c['attachments'] = $this->attachments($c['files'], $c['body']);
                $c['filesJSON'] = json_encode($c['files']);
                if ($nodeComment) {
                    $c['type'] = 'node';
                    $c['id'] = $node['id'];
                    $c['report'] = ($node['points'] < 5 || strpos($c['body'], 'http') !== false);
                    $nodeComment = false;
                }

                $posts[] = $c;
            }
        }

        $editor_contents = [
            'title'          => $node['title'],
            'form_handler' => '/node/' . $nid . '/comment',
            'hasFile'        => true
        ];
        $editor = new Template('editor_bbcode', $editor_contents);

        $contents += [
            'posts'  => $posts,
            'editor' => $editor
        ];

        $this->var['content'] = new Template('node_forum_topic', $contents);
    }

    private function authorPanel($info)
    {
        static $authorPanels = [];

        if (!(array_key_exists('uid', $info) && $info['uid'] > 0)) {
            return null;
        }

        if (!array_key_exists($info['uid'], $authorPanels)) {
            $authorPanelCache = $this->getIndependentCache('ap' . $info['uid']);
            $authorPanel = $authorPanelCache->fetch();
            if (!$authorPanel) {
                $info['joinTime'] = date('m/d/Y', (int) $info['join_time']);
                $info['sex'] = isset($info['sex']) ? ($info['sex'] == 1 ? '男' : '女') : '未知';
                if (empty($info['avatar'])) {
                    $info['avatar'] = '/data/avatars/avatar0' . rand(1, 5) . '.jpg';
                }
                $info['city'] = $this->request->getCityFromIP($info['access_ip']);
                $authorPanel = new Template('author_panel_forum', $info);
                $authorPanelCache->store($authorPanel);
            }
            $authorPanels[$info['uid']] = $authorPanel;
        }

        return $authorPanels[$info['uid']];
    }

    private function attachments(array $files, string $body)
    {
        $fileElements = [];
        $imageElements = [];

        foreach ($files as $f) {
            $tmp = explode('.', $f['path']);
            $type = array_pop($tmp);
            switch ($type) {
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                    $isImage = true;
                    $bbcode = '[img]' . $f['path'] . '[/img]';
                    break;
                default:
                    $isImage = false;
                    $bbcode = '[file="' . $f['path'] . '"]' . $f['name'] . '[/file]';
            }

            if (strpos($body, $bbcode) !== false) {
                continue;
            }

            if ($isImage) {
                $imageElements[] = new HTMLElement('figure', [
                    new HTMLElement('figcaption', $f['name']),
                    new HTMLElement('img', null, ['src' => $f['path'], 'alt' => '图片加载失败 : ' . $f['name']])]);
            } else {
                $fileElements[] = Template::link($f['name'], $f['path']);
            }
        }

        $attachments = null;
        if (sizeof($imageElements) > 0) {
            $attachments .= new HTMLElement('div', $imageElements, ['class' => 'attach_images']);
        }
        if (sizeof($fileElements) > 0) {
            $attachments .= new HTMLElement('div', $fileElements, ['class' => 'attach_files']);
        }

        return $attachments;
    }

    private function displayYellowPage($nid)
    {
        $nodeObj = new NodeObject();
        $node = $nodeObj->getYellowPageNode($nid);
        $tags = $nodeObj->getTags($nid);

        $this->var['head_title'] = $node['title'];
        $this->var['head_description'] = $node['title'];

        if (is_null($node)) {
            $this->pageNotFound();
        }

        $breadcrumb = [];
        foreach ($tags as $i => $t) {
            $breadcrumb[$t['name']] = ($i === self::$city->tidYp ? '/yp' : ('/yp/' . $i));
        }
        $breadcrumb[$node['title']] = null;

        list($pageNo, $pageCount) = $this->getPagerInfo($node['comment_count'], self::COMMENTS_PER_PAGE);
        $pager = Template::pager($pageNo, $pageCount, '/node/' . $nid);

        $postNumStart = ($pageNo - 1) * self::COMMENTS_PER_PAGE + 1;

        $contents = [
            'nid' => $nid,
            'cid' => $tags[2]['cid'],
            'commentCount' => $node['comment_count'],
            'status' => $node['status'],
            'breadcrumb' => Template::breadcrumb($breadcrumb),
            'pager' => $pager,
            'postNumStart' => $postNumStart,
            'ajaxURI' => '/api/viewcount/' . $nid
        ];

        $node['type'] = 'node';

        $nodeComment = ($pageNo == 1);
        $comments = $nodeObj->getYellowPageNodeComments($nid, self::COMMENTS_PER_PAGE, ($pageNo - 1) * self::COMMENTS_PER_PAGE);

        $cmts = [];
        if (sizeof($comments) > 0) {
            foreach ($comments as $c) {
                if ($nodeComment) {
                    // show node details as the first post
                    try {
                        $node['HTMLbody'] = BBCode::parse($c['body']);
                    } catch (Exception $e) {
                        $node['HTMLbody'] = nl2br($c['body']);
                        $this->logger->error($e->getMessage(), $e->getTrace());
                    }
                    $node['attachments'] = $this->attachments($c['files'], $c['body']);
                    //$node['filesJSON'] = json_encode($node['files']);

                    $nodeComment = false;
                } else {
                    $c['id'] = $c['id'];
                    $c['type'] = 'comment';
                    $c['createTime'] = date('m/d/Y H:i', (int) $c['create_time']);
                    if ($c['lastModifiedTime']) {
                        $c['lastModifiedTime'] = date('m/d/Y H:i', (int) $c['last_modified_time']);
                    }
                    $c['HTMLbody'] = nl2br($c['body']);
                    try {
                        $c['HTMLbody'] = BBCode::parse($c['body']);
                    } catch (Exception $e) {
                        $c['HTMLbody'] = nl2br($c['body']);
                        $this->logger->error($e->getMessage(), $e->getTrace());
                    }

                    $cmts[] = $c;
                }
            }
        }

        $editor_contents = [
            'form_handler' => '/node/' . $nid . '/comment'
        ];
        $editor = new Template('editor_bbcode', $editor_contents);

        $contents += [
            'node' => $node,
            'comments' => $cmts,
            'editor' => $editor
        ];

        $this->var['content'] = new Template('node_yellow_page', $contents);
    }
}
