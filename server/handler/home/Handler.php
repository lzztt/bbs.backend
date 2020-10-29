<?php declare(strict_types=1);

namespace site\handler\home;

use lzx\cache\SegmentCache;
use lzx\exception\ErrorMessage;
use lzx\html\Template;
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
                ->setHotForumTopicsWeekly($this->getHotForumTopics(15, 7))
                ->setHotForumTopicsMonthly($this->getHotForumTopics(15, 30))
                ->setLatestYellowPages($this->getLatestYellowPages(15))
                ->setLatestForumTopicReplies($this->getLatestForumTopicReplies(15))
                ->setLatestYellowPageReplies(($this->getLatestYellowPageReplies(15)))
                ->setImageSlider($this->getImageSlider())
        );
    }

    private function bayHome(): void
    {
        $this->html->setContent(
            (new Home())
                ->setCity(self::$city->id)
                ->setLatestForumTopics($this->getLatestForumTopics(20))
                ->setHotForumTopicsMonthly($this->getHotForumTopics(20, 30))
                ->setLatestForumTopicReplies($this->getLatestForumTopicReplies(20))
                ->setImageSlider($this->getImageSlider())
        );
    }

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

    private function getHotForumTopics(int $count, int $days): Template
    {
        $ulCache = $this->cache->getSegment('hotForumTopics' . $days);
        $ul = $ulCache->getData();
        if (!$ul) {
            $arr = [];
            $start = $this->request->timestamp - $days * 86400;

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
