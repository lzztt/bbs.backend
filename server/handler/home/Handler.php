<?php declare(strict_types=1);

namespace site\handler\home;

use lzx\cache\SegmentCache;
use lzx\exception\ErrorMessage;
use lzx\html\Template;
use site\City;
use site\Controller;
use site\dbobject\Activity;
use site\dbobject\Image;
use site\dbobject\Node;
use site\dbobject\Tag;
use site\gen\theme\roselife\Home;
use site\gen\theme\roselife\HomeItemlist;
use site\gen\theme\roselife\ImageSlider;

class Handler extends Controller
{
    public function run(): void
    {
        $this->cache = $this->getPageCache();

        switch (self::$city->domain) {
            case 'houstonbbs.com':
                $this->houstonHome();
                break;
            case 'dallasbbs.com':
            case 'bayever.com':
                $this->bayHome();
                break;
            default:
                throw new ErrorMessage('unsupported site: ' . self::$city->domain);
        }
    }

    private function houstonHome(): void
    {
        $this->html->setContent(
            (new Home())
                ->setCity(self::$city->id)
                ->setRecentActivities($this->getRecentActivities())
                ->setLatestForumTopics($this->getLatestForumTopics(15))
                ->setHotForumTopics($this->getHotForumTopics(15))
                ->setLatestYellowPages($this->getLatestYellowPages(15))
                ->setLatestForumTopicReplies($this->getLatestForumTopicReplies(15))
                ->setLatestYellowPageReplies(($this->getLatestYellowPageReplies(15)))
                ->setImageSlider($this->getImageSlider())
        );
    }

    private function bayHome(): void
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

        $this->html->setContent(
            (new Home())
                ->setCity(self::$city->id)
                ->setLatestForumTopics($this->getLatestForumTopics(10))
                ->setHotForumTopics($this->getHotForumTopics(10))
                ->setLatestForumTopicReplies($this->getLatestForumTopicReplies(10))
                ->setImageSlider($this->getImageSlider())
                ->setGroups($groupTrees)
                ->setNodeInfo($nodeInfo)
        );
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

    private function getImageSlider(): Template
    {
        $ulCache = $this->cache->getSegment('imageSlider');
        $ul = $ulCache->getData();
        if (!$ul) {
            $img = new Image();
            $images = $img->getRecentImages(self::$city->id);
            shuffle($images);

            $ul = (new ImageSlider())->setImages($images);

            $ulCache->setData($ul);

            foreach ($images as $i) {
                $ulCache->addParent('/node/' . $i['nid']);
            }
            $this->getCacheEvent('ImageUpdate')->addListener($ulCache);
        }

        return $ul;
    }

    private function getLatestForumTopics(int $count): Template
    {
        $ulCache = $this->cache->getSegment('latestForumTopics');
        $ul = $ulCache->getData();
        if (!$ul) {
            $arr = [];

            foreach ((new Node())->getLatestForumTopics(self::$city->tidForum, $count) as $n) {
                $arr[] = [
                    'after' => date('H:i', (int) $n['create_time']),
                    'uri' => '/node/' . $n['nid'],
                    'text' => $n['title']
                ];
            }
            $ul = $this->linkNodeList($arr, $ulCache);
        }
        $this->getCacheEvent('ForumNode')->addListener($ulCache);

        return $ul;
    }

    private function getHotForumTopics(int $count): Template
    {
        $ulCache = $this->cache->getSegment('hotForumTopics');
        $ul = $ulCache->getData();
        if (!$ul) {
            $arr = [];
            // 1 week for houstonbbs, 3 weeks for other cities
            $start = $this->request->timestamp - (self::$city->id === City::HOUSTON ? 604800 : 604800 * 3);

            foreach ((new Node())->getHotForumTopics(self::$city->tidForum, $count, $start) as $i => $n) {
                $arr[] = [
                    'after' => $i + 1,
                    'uri' => '/node/' . $n['nid'],
                    'text' => $n['title']
                ];
            }
            $ul = $this->linkNodeList($arr, $ulCache);
        }

        return $ul;
    }

    private function getLatestYellowPages(int $count): Template
    {
        $ulCache = $this->cache->getSegment('latestYellowPages');
        $ul = $ulCache->getData();
        if (!$ul) {
            $arr = [];

            foreach ((new Node())->getLatestYellowPages(self::$city->tidYp, $count) as $n) {
                $arr[] = [
                    'after' => date('m/d', (int) $n['exp_time']),
                    'uri' => '/node/' . $n['nid'],
                    'text' => $n['title']
                ];
            }
            $ul = $this->linkNodeList($arr, $ulCache);
        }
        $this->getCacheEvent('YellowPageNode')->addListener($ulCache);

        return $ul;
    }

    private function getLatestForumTopicReplies(int $count): Template
    {
        $ulCache = $this->cache->getSegment('latestForumTopicReplies');
        $ul = $ulCache->getData();
        if (!$ul) {
            $arr = [];

            foreach ((new Node())->getLatestForumTopicReplies(self::$city->tidForum, $count) as $n) {
                $arr[] = [
                    'after' => $n['comment_count'],
                    'uri' => '/node/' . $n['nid'] . '?p=l#comment' . $n['last_cid'],
                    'text' => $n['title']
                ];
            }
            $ul = $this->linkNodeList($arr, $ulCache);
        }
        $this->getCacheEvent('ForumComment')->addListener($ulCache);

        return $ul;
    }

    private function getLatestYellowPageReplies(int $count): Template
    {
        $ulCache = $this->cache->getSegment('latestYellowPageReplies');
        $ul = $ulCache->getData();
        if (!$ul) {
            $arr = [];

            foreach ((new Node())->getLatestYellowPageReplies(self::$city->tidYp, $count) as $n) {
                $arr[] = [
                    'after' => $n['comment_count'],
                    'uri' => '/node/' . $n['nid'] . '?p=l#comment' . $n['last_cid'],
                    'text' => $n['title']
                ];
            }
            $ul = $this->linkNodeList($arr, $ulCache);
        }
        $this->getCacheEvent('YellowPageComment')->addListener($ulCache);

        return $ul;
    }

    private function getRecentActivities(): Template
    {
        $ulCache = $this->cache->getSegment('recentActivities');
        $ul = $ulCache->getData();
        if (!$ul) {
            $arr = [];

            foreach ((new Activity())->getRecentActivities(10, $this->request->timestamp) as $n) {
                $arr[] = [
                    'class' => 'activity_' . $n['class'],
                    'after' => date('m/d', (int) $n['start_time']),
                    'uri' => '/node/' . $n['nid'],
                    'text' => $n['title']
                ];
            }
            $ul = $this->linkNodeList($arr, $ulCache);
        }

        return $ul;
    }

    private function linkNodeList(array $arr, SegmentCache $ulCache): Template
    {
        $ul = (new HomeItemlist())->setData($arr);

        $ulCache->setData($ul);
        foreach ($arr as $n) {
            $ulCache->addParent(strtok($n['uri'], '?#'));
        }

        return $ul;
    }
}
