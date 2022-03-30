<?php

declare(strict_types=1);

namespace site\handler\node;

use lzx\exception\NotFound;
use lzx\html\HtmlElement;
use lzx\html\Template;
use site\dbobject\Node as NodeObject;
use site\dbobject\Tag;
use site\gen\theme\roselife\AuthorPanelForum;
use site\gen\theme\roselife\NodeForumTopic;
use site\gen\theme\roselife\NodeYellowPage;
use site\handler\node\Node;

class Handler extends Node
{
    private const JSON_OPTIONS = JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public function run(): void
    {
        $this->cache = $this->getPageCache();

        $nid = $this->getNodeId();
        $this->displayForumTopic($nid);

        $this->getCacheEvent('NodeUpdate', $nid)->addListener($this->cache);
    }

    private function displayForumTopic(int $nid): void
    {
        $nodeObj = new NodeObject();
        $node = $nodeObj->getForumNode($nid);
        if (!$node) {
            throw new NotFound();
        }

        $this->html
            ->setHeadTitle($node['title'])
            ->setHeadDescription($node['title']);

        $breadcrumb = ['首页' => '/'];
        $tid = (int) $node['tid'];
        $tag = new Tag($tid, 'name');
        $breadcrumb[$tag->name] = '/forum/' . $tid;
        $breadcrumb[$node['title']] = null;

        list($pageNo, $pageCount) = $this->getPagerInfo((int) $node['comment_count'], self::COMMENTS_PER_PAGE);
        $pager = HtmlElement::pager($pageNo, $pageCount, '/node/' . $node['id']);

        $page = (new NodeForumTopic())
            ->setCity(self::$city->id)
            ->setNid($nid)
            ->setTid($tid)
            ->setCommentCount($node['comment_count'] - 1)
            ->setBreadcrumb(HtmlElement::breadcrumb($breadcrumb))
            ->setPager($pager)
            ->setAjaxUri('/api/viewcount/' . $nid);

        $posts = [];

        $authorPanelInfo = [
            'uid' => null,
            'username' => null,
            'avatar' => null,
        ];

        $nodeComment = ($pageNo == 1);
        $nodeObj = new NodeObject();
        $comments = $nodeObj->getForumNodeComments($nid, self::COMMENTS_PER_PAGE, ($pageNo - 1) * self::COMMENTS_PER_PAGE);

        if (sizeof($comments) > 0) {
            foreach ($comments as $c) {
                $c['type'] = 'comment';
                $c['createTime'] = (int) $c['create_time'];
                $c['HTMLbody'] = $c['body'];
                $c['authorPanel'] = $this->authorPanel(array_intersect_key($c, $authorPanelInfo));
                $c['city'] = $c['access_ip'] ? self::getLocationFromIp($c['access_ip'], false) : 'N/A';
                $c['attachments'] = $this->attachments($c['files'], $c['body']);
                $c['quoteJson'] = json_encode([
                    'nodeId' => $nid,
                    'body' => $this->quote($c['username'], $c['body'])
                ], self::JSON_OPTIONS);
                if ($nodeComment) {
                    $c['type'] = 'node';
                    // $c['id'] = $node['id'];
                    // $c['report'] = ($node['points'] < 5 || strpos($c['body'], 'http') !== false);
                    $c['editJson'] = json_encode([
                        'tagId' => $tag->id,
                        'nodeId' => $nid,
                        'title' => $node['title'],
                        'body' => $c['body'],
                        'images' => $c['files']
                    ], self::JSON_OPTIONS);
                    $nodeComment = false;
                } else {
                    $c['editJson'] = json_encode([
                        'nodeId' => $nid,
                        'commentId' => $c['id'],
                        'body' => $c['body'],
                        'images' => $c['files']
                    ], self::JSON_OPTIONS);
                }
                $posts[] = $c;
            }
        }

        $this->html->setContent($page->setPosts($posts));
    }

    private function quote(string $name, string $body): string
    {
        $lines = [];
        foreach (explode(PHP_EOL, $body) as $line) {
            if (!$lines && !trim($line)) {
                continue;
            }
            if (!str_starts_with($line, '> ')) {
                $lines[] = $line;
            }
        }

        while ($lines && !trim(end($lines))) {
            array_pop($lines);
        }

        return '> **' . $name . ':**  ' . PHP_EOL . '> ' . implode(PHP_EOL . '> ', $lines) . PHP_EOL . PHP_EOL;
    }

    private function authorPanel(array $info): Template
    {
        static $authorPanels = [];

        if (!(array_key_exists('uid', $info) && $info['uid'] > 0)) {
            return Template::fromStr('');
        }

        if (!array_key_exists($info['uid'], $authorPanels)) {
            if (!$info['avatar']) {
                $info['avatar'] = '';
            }
            $authorPanel = (new AuthorPanelForum())
                ->setUid((int) $info['uid'])
                ->setUsername($info['username'])
                ->setAvatar($info['avatar']);
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
}
