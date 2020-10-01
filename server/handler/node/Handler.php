<?php declare(strict_types=1);

namespace site\handler\node;

use Exception;
use lzx\core\BBCodeRE as BBCode;
use lzx\db\MemStore;
use lzx\exception\NotFound;
use lzx\html\HtmlElement;
use lzx\html\Template;
use site\dbobject\Node as NodeObject;
use site\gen\theme\roselife\AuthorPanelForum;
use site\gen\theme\roselife\EditorBbcode;
use site\gen\theme\roselife\NodeForumTopic;
use site\gen\theme\roselife\NodeYellowPage;
use site\handler\node\Node;

class Handler extends Node
{
    public function run(): void
    {
        $this->cache = $this->getPageCache();

        list($nid, $type) = $this->getNodeType();
        switch ($type) {
            case self::FORUM_TOPIC:
                $this->displayForumTopic($nid);
                break;
            case self::YELLOW_PAGE:
                $this->displayYellowPage($nid);
                break;
        }

        $this->getCacheEvent('NodeUpdate', $nid)->addListener($this->cache);
    }

    private function displayForumTopic(int $nid): void
    {
        $rateLimiter = MemStore::getRedis(3);
        $key = date("d") . ':' . ($this->session->get('uid') ?: $this->request->ip);
        $oneDay = 86400;
        $skip = false;
        if ($rateLimiter->sCard($key) > 50) {
            // check bots
            $isGoogleBot = false;
            if ($this->request->isRobot()) {
                $botKey = 'b:' . $this->request->ip;
                if ($rateLimiter->exists($botKey)) {
                    $isGoogleBot = boolval($rateLimiter->get($botKey));
                } else {
                    $isGoogleBot = $this->request->isGoogleBot();
                    $rateLimiter->set($botKey, $isGoogleBot ? '1' : '0', $oneDay);
                }
            }

            if ($isGoogleBot) {
                $skip = true;
            } else {
                // mail error log
                if (!$rateLimiter->exists($key . ':log')) {
                    $this->logger->error('rate limit ' . $this->request->ip);
                    $rateLimiter->set($key . ':log', '', $oneDay);
                }
                throw new NotFound();
            }
        }

        if (!$skip) {
            $rateLimiter->sAdd($key, $nid);
            $rateLimiter->expire($key, 86400);
        }

        $nodeObj = new NodeObject();
        $node = $nodeObj->getForumNode($nid);
        if (!$node) {
            throw new NotFound();
        }

        $tags = $nodeObj->getTags($nid);

        $this->html
            ->setHeadTitle($node['title'])
            ->setHeadDescription($node['title']);

        $breadcrumb = [];
        foreach ($tags as $i => $t) {
            $breadcrumb[$t['name']] = ($i === self::$city->tidForum ? '/forum' : ('/forum/' . $i));
        }
        $breadcrumb[$node['title']] = null;

        list($pageNo, $pageCount) = $this->getPagerInfo((int) $node['comment_count'], self::COMMENTS_PER_PAGE);
        $pager = HtmlElement::pager($pageNo, $pageCount, '/node/' . $node['id']);

        $postNumStart = ($pageNo - 1) * self::COMMENTS_PER_PAGE; // first page start from the node and followed by comments

        $page = (new NodeForumTopic())
            ->setCity(self::$city->id)
            ->setNid($nid)
            ->setTid((int) $node['tid'])
            ->setCommentCount($node['comment_count'] - 1)
            ->setBreadcrumb(HtmlElement::breadcrumb($breadcrumb))
            ->setPager($pager)
            ->setPostNumStart($postNumStart)
            ->setAjaxUri('/api/viewcount/' . $nid);

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
                $c['lastModifiedTime'] = empty($c['lastModifiedTime']) ? '' : date('m/d/Y H:i', (int) $c['last_modified_time']);

                try {
                    $c['HTMLbody'] = BBCode::parse($c['body']);
                } catch (Exception $e) {
                    $c['HTMLbody'] = nl2br($c['body']);
                    $this->logger->logException($e);
                }
                $c['authorPanel'] = $this->authorPanel(array_intersect_key($c, $authorPanelInfo));
                $c['city'] = $c['access_ip'] ? self::getLocationFromIp($c['access_ip'], false) : 'N/A';
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

        $this->html->setContent(
            $page->setPosts($posts)
                ->setEditor(
                    (new EditorBbcode())
                        ->setTitle($node['title'])
                        ->setFormHandler('/node/' . $nid . '/comment')
                        ->setHasFile(true)
                )
        );
    }

    private function authorPanel(array $info): Template
    {
        static $authorPanels = [];

        if (!(array_key_exists('uid', $info) && $info['uid'] > 0)) {
            return Template::fromStr('');
        }

        if (!array_key_exists($info['uid'], $authorPanels)) {
            $authorPanelCache = $this->getIndependentCache('ap' . $info['uid']);
            $authorPanel = $authorPanelCache->getData();
            if (!$authorPanel) {
                $info['joinTime'] = date('m/d/Y', (int) $info['join_time']);
                if (!$info['avatar']) {
                    $info['avatar'] = '';
                }
                $authorPanel = (new AuthorPanelForum())
                    ->setUid((int) $info['uid'])
                    ->setUsername($info['username'])
                    ->setAvatar($info['avatar'])
                    ->setJoinTime($info['joinTime'])
                    ->setPoints((int) $info['points']);
                $authorPanelCache->setData($authorPanel);
            }
            $authorPanels[$info['uid']] = $authorPanel;
        }

        return $authorPanels[$info['uid']];
    }

    private function attachments(array $files, string $body): string
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
                $imageElements[] = new HtmlElement('figure', [
                    new HtmlElement('figcaption', $f['name']),
                    new HtmlElement('img', null, ['src' => $f['path'], 'alt' => '图片加载失败 : ' . $f['name']])
                ]);
            } else {
                $fileElements[] = HtmlElement::link($f['name'], $f['path']);
            }
        }

        $attachments = '';
        if (sizeof($imageElements) > 0) {
            $attachments .= new HtmlElement('div', $imageElements, ['class' => 'attach_images']);
        }
        if (sizeof($fileElements) > 0) {
            $attachments .= new HtmlElement('div', $fileElements, ['class' => 'attach_files']);
        }

        return $attachments;
    }

    private function displayYellowPage(int $nid): void
    {
        $nodeObj = new NodeObject();
        $node = $nodeObj->getYellowPageNode($nid);
        if (!$node) {
            throw new NotFound();
        }

        $tags = $nodeObj->getTags($nid);

        $this->var['head_title'] = $node['title'];
        $this->var['head_description'] = $node['title'];

        $breadcrumb = [];
        foreach ($tags as $i => $t) {
            $breadcrumb[$t['name']] = ($i === self::$city->tidYp ? '/yp' : ('/yp/' . $i));
        }
        $breadcrumb[$node['title']] = null;

        list($pageNo, $pageCount) = $this->getPagerInfo((int) $node['comment_count'], self::COMMENTS_PER_PAGE);
        $pager = HtmlElement::pager($pageNo, $pageCount, '/node/' . $nid);

        $postNumStart = ($pageNo - 1) * self::COMMENTS_PER_PAGE + 1;

        $page = (new NodeYellowPage())
            ->setNid($nid)
            ->setCommentCount((int) $node['comment_count'])
            ->setBreadcrumb(HtmlElement::breadcrumb($breadcrumb))
            ->setPager($pager)
            ->setPostNumStart($postNumStart)
            ->setAjaxUri('/api/viewcount/' . $nid);

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
                        $this->logger->logException($e);
                    }
                    $node['attachments'] = $this->attachments($c['files'], $c['body']);
                    //$node['filesJSON'] = json_encode($node['files']);

                    $nodeComment = false;
                } else {
                    $c['type'] = 'comment';
                    $c['createTime'] = date('m/d/Y H:i', (int) $c['create_time']);
                    $c['lastModifiedTime'] = empty($c['lastModifiedTime']) ? '' : date('m/d/Y H:i', (int) $c['last_modified_time']);
                    try {
                        $c['HTMLbody'] = BBCode::parse($c['body']);
                    } catch (Exception $e) {
                        $c['HTMLbody'] = nl2br($c['body']);
                        $this->logger->logException($e);
                    }

                    $cmts[] = $c;
                }
            }
        }


        $page->setNode($node)
            ->setComments(($cmts))
            ->setEditor(
                (new EditorBbcode())
                    ->setFormHandler('/node/' . $nid . '/comment')
            );

        $this->html->setContent($page);
    }
}
