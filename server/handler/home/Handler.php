<?php declare(strict_types=1);

namespace site\handler\home;

use lzx\cache\PageCache;
use lzx\cache\SegmentCache;
use lzx\html\Template;
use site\Controller;
use site\dbobject\Activity;
use site\dbobject\Image;
use site\dbobject\Node;
use site\dbobject\Tag;

class Handler extends Controller
{
    public function run(): void
    {
        $this->cache = new PageCache($this->request->uri);

        $func = self::$city->uriName . 'Home';
        if (method_exists($this, $func)) {
            $this->$func();
        } else {
            $this->error('unsupported site: ' . self::$city->uriName);
        }
    }

    private function houstonHome(): void
    {
        $content = [
            'recentActivities' => $this->getRecentActivities(),
            'latestForumTopics' => $this->getLatestForumTopics(15),
            'hotForumTopics' => $this->getHotForumTopics(15),
            'latestYellowPages' => $this->getLatestYellowPages(15),
            'latestForumTopicReplies' => $this->getLatestForumTopicReplies(15),
            'latestYellowPageReplies' => $this->getLatestYellowPageReplies(15),
            'imageSlider' => $this->getImageSlider(),
        ];
        $this->var['content'] = new Template('home', $content);
    }

    // BEGIN DALLAS HOME
    private function dallasHome(): void
    {
        $tag = new Tag(self::$city->tidForum, 'id');
        $tagTree = $tag->getTagTree();

        $nodeInfo = [];
        $groupTrees = [];
        foreach ($tagTree[$tag->id]['children'] as $group_id) {
            $groupTrees[$group_id] = [];
            $group = $tagTree[$group_id];
            $groupTrees[$group_id][$group_id] = $group;
            foreach ($group['children'] as $board_id) {
                $groupTrees[$group_id][$board_id] = $tagTree[$board_id];
                $nodeInfo[$board_id] = $this->nodeInfo($board_id);
                $this->cache->addParent('/forum/' . $board_id);
            }
        }

        $content = [
            'latestForumTopics' => $this->getLatestForumTopics(10),
            'hotForumTopics' => $this->getHotForumTopics(10),
            'latestForumTopicReplies' => $this->getLatestForumTopicReplies(10),
            'imageSlider' => $this->getImageSlider(),
            'groups' => $groupTrees,
            'nodeInfo' => $nodeInfo
        ];

        $this->var['content'] = new Template('home', $content);
    }

    private function austinHome(): void
    {
        $tag = new Tag(self::$city->tidForum, 'id');
        $tagTree = $tag->getTagTree();

        $nodeInfo = [];
        $groupTrees = [];
        foreach ($tagTree[$tag->id]['children'] as $group_id) {
            $groupTrees[$group_id] = [];
            $group = $tagTree[$group_id];
            $groupTrees[$group_id][$group_id] = $group;
            foreach ($group['children'] as $board_id) {
                $groupTrees[$group_id][$board_id] = $tagTree[$board_id];
                $nodeInfo[$board_id] = $this->nodeInfo($board_id);
                $this->cache->addParent('/forum/' . $board_id);
            }
        }

        $content = [
            'latestForumTopics' => $this->getLatestForumTopics(10),
            'hotForumTopics' => $this->getHotForumTopics(10),
            'latestForumTopicReplies' => $this->getLatestForumTopicReplies(10),
            'imageSlider' => $this->getImageSlider(),
            'groups' => $groupTrees,
            'nodeInfo' => $nodeInfo
        ];

        $this->var['content'] = new Template('home', $content);
    }

    protected function nodeInfo(int $tid): array
    {
        $tag = new Tag($tid, 'id');

        foreach ($tag->getNodeInfo($tid) as $v) {
            $v['create_time'] = date('m/d/Y H:i', (int) $v['create_time']);
            if ($v['cid'] == 0) {
                $node = $v;
            } else {
                $comment = $v;
            }
        }
        return ['node' => $node, 'comment' => $comment];
    }

    // END DALLAS HOME

    private function getImageSlider(): string
    {
        $ulCache = $this->cache->getSegment('imageSlider');
        $ul = $ulCache->fetch();
        if (!$ul) {
            $img = new Image();
            $images = $img->getRecentImages(self::$city->id);
            shuffle($images);

            $content['images'] = $images;
            $ul = (string) new Template('image_slider', $content);

            $ulCache->store($ul);

            foreach ($images as $i) {
                $ulCache->addParent('/node/' . $i['nid']);
            }
            $this->getCacheEvent('ImageUpdate')->addListener($ulCache);
        }

        return $ul;
    }

    private function getLatestForumTopics(int $count): string
    {
        $ulCache = $this->cache->getSegment('latestForumTopics');
        $ul = $ulCache->fetch();
        if (!$ul) {
            $arr = [];

            foreach ((new Node())->getLatestForumTopics(self::$city->tidForum, $count) as $n) {
                $arr[] = ['after' => date('H:i', (int) $n['create_time']),
                    'uri' => '/node/' . $n['nid'],
                    'text' => $n['title']];
            }
            $ul = $this->linkNodeList($arr, $ulCache);
        }
        $this->getCacheEvent('ForumNode')->addListener($ulCache);

        return $ul;
    }

    private function getHotForumTopics(int $count): string
    {
        $ulCache = $this->cache->getSegment('hotForumTopics');
        $ul = $ulCache->fetch();
        if (!$ul) {
            $arr = [];
            // 1 week for houstonbbs, 2 weeks for other cities
            $start = (self::$city->id == 1 ? $this->request->timestamp - 604800 : $this->request->timestamp - 604800 * 2);

            foreach ((new Node())->getHotForumTopics(self::$city->tidForum, $count, $start) as $i => $n) {
                $arr[] = ['after' => $i + 1,
                    'uri' => '/node/' . $n['nid'],
                    'text' => $n['title']];
            }
            $ul = $this->linkNodeList($arr, $ulCache);
        }

        return $ul;
    }

    private function getLatestYellowPages(int $count): array
    {
        $ulCache = $this->cache->getSegment('latestYellowPages');
        $cache = $ulCache->fetch();
        $ul = $cache ? unserialize($cache) : [];
        if (!$ul) {
            $ul = [];
            $ypGroups = array_chunk((new Node())->getLatestYellowPages(self::$city->tidYp, $count * 2), $count);

            foreach ($ypGroups as $yps) {
                $arr = [];
                foreach ($yps as $n) {
                    $arr[] = ['after' => date('m/d', (int) $n['exp_time']),
                        'uri' => '/node/' . $n['nid'],
                        'text' => $n['title']];
                }
                $ul[] = $this->linkNodeList($arr, $ulCache);
            }

            $ulCache->store(serialize($ul));
        }
        $this->getCacheEvent('YellowPageNode')->addListener($ulCache);

        return $ul;
    }

    private function getLatestForumTopicReplies(int $count): string
    {
        $ulCache = $this->cache->getSegment('latestForumTopicReplies');
        $ul = $ulCache->fetch();
        if (!$ul) {
            $arr = [];

            foreach ((new Node())->getLatestForumTopicReplies(self::$city->tidForum, $count) as $n) {
                $arr[] = ['after' => $n['comment_count'],
                    'uri' => '/node/' . $n['nid'] . '?p=l#comment' . $n['last_cid'],
                    'text' => $n['title']];
            }
            $ul = $this->linkNodeList($arr, $ulCache);
        }
        $this->getCacheEvent('ForumComment')->addListener($ulCache);

        return $ul;
    }

    private function getLatestYellowPageReplies(int $count): string
    {
        $ulCache = $this->cache->getSegment('latestYellowPageReplies');
        $ul = $ulCache->fetch();
        if (!$ul) {
            $arr = [];

            foreach ((new Node())->getLatestYellowPageReplies(self::$city->tidYp, $count) as $n) {
                $arr[] = ['after' => $n['comment_count'],
                    'uri' => '/node/' . $n['nid'] . '?p=l#comment' . $n['last_cid'],
                    'text' => $n['title']];
            }
            $ul = $this->linkNodeList($arr, $ulCache);
        }
        $this->getCacheEvent('YellowPageComment')->addListener($ulCache);

        return $ul;
    }

    private function getRecentActivities(): string
    {
        $ulCache = $this->cache->getSegment('recentActivities');
        $ul = $ulCache->fetch();
        if (!$ul) {
            $arr = [];

            foreach ((new Activity())->getRecentActivities(10, $this->request->timestamp) as $n) {
                $arr[] = ['class' => 'activity_' . $n['class'],
                    'after' => date('m/d', (int) $n['start_time']),
                    'uri' => '/node/' . $n['nid'],
                    'text' => $n['title']];
            }
            $ul = $this->linkNodeList($arr, $ulCache);
        }

        return $ul;
    }

    private function linkNodeList(array $arr, SegmentCache $ulCache): string
    {
        $ul = (string) new Template('home_itemlist', ['data' => $arr]);

        $ulCache->store($ul);
        foreach ($arr as $n) {
            $ulCache->addParent(strtok($n['uri'], '?#'));
        }

        return $ul;
    }
}
