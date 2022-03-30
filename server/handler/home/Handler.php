<?php

declare(strict_types=1);

namespace site\handler\home;

use lzx\cache\SegmentCache;
use lzx\exception\ErrorMessage;
use lzx\html\Template;
use site\City;
use site\Controller;
use site\dbobject\Image;
use site\dbobject\Node;
use site\gen\theme\roselife\Home;
use site\gen\theme\roselife\HomeItemlist;
use site\gen\theme\roselife\ImageSlider;

class Handler extends Controller
{
    private $lastModifiedTime = 0;

    public function run(): void
    {
        $this->cache = $this->getPageCache();

        switch (self::$city->id) {
            case City::HOUSTON:
                $this->houstonHome();
                break;
            case City::DALLAS:
            case City::SFBAY:
                $this->bayHome();
                break;
            default:
                throw new ErrorMessage('unsupported site: ' . self::$city->domain);
        }
    }

    private function houstonHome(): void
    {
        $nodes = (new Node())->getLatestYellowPages(15);
        $count = intdiv(count($nodes), 3);

        $this->html->setContent(
            (new Home())
                ->setCity(self::$city->id)
                ->setImageSlider($this->getImageSlider())
                ->setLatestForumTopics($this->getLatestForumTopics(20 - $count, array_splice($nodes, 0, $count)))
                ->setLatestForumTopicReplies($this->getLatestForumTopicReplies(20 - $count, array_splice($nodes, 0, $count)))
                ->setHotForumTopicsWeekly($this->getHotForumTopics(20 - count($nodes), 7, $nodes))
                ->setHotForumTopicsMonthly($this->getHotForumTopics(10, 30))
        );
        $this->html->setLastModifiedTime($this->lastModifiedTime);
    }

    private function bayHome(): void
    {
        $this->html->setContent(
            (new Home())
                ->setCity(self::$city->id)
                ->setImageSlider($this->getImageSlider())
                ->setLatestForumTopics($this->getLatestForumTopics(20))
                ->setLatestForumTopicReplies($this->getLatestForumTopicReplies(20))
                ->setHotForumTopicsMonthly($this->getHotForumTopics(20, 30))
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

    private function getLatestForumTopics(int $count, array $ads = []): Template
    {
        $ulCache = $this->cache->getSegment('latestForumTopics');
        $ul = $ulCache->getData();
        if (!$ul) {
            $arr = [];

            $nodes = (new Node())->getLatestForumTopics(self::$city->tidForum, $count);
            $lastModifiedTime = $nodes ? (int) $nodes[0]['create_time'] : 0;
            foreach ($nodes as $n) {
                $arr[] = [
                    'time' => (int) $n['create_time'],
                    'method' => 'toTime',
                    'uri' => '/node/' . $n['nid'],
                    'text' => $n['title']
                ];
            }
            foreach ($ads as $n) {
                $arr[] = [
                    'time' => (int) $n['exp_time'],
                    'method' => 'toDate',
                    'uri' => '/node/' . $n['nid'],
                    'text' => '🏷️ ' . $n['title']
                ];
            }
            $ul = $this->linkNodeList($arr, $lastModifiedTime, $ulCache);

            $this->getCacheEvent('ForumNode')->addListener($ulCache);
            if (self::$city->id === City::HOUSTON) {
                $this->getCacheEvent('YellowPageUpdate')->addListener($ulCache);
            }
        } else {
            $lastModifiedTime = $this->getNodeListTime((string) $ul);
        }
        $this->lastModifiedTime = max($this->lastModifiedTime, $lastModifiedTime);

        return $ul;
    }

    private function getHotForumTopics(int $count, int $days, array $ads = []): Template
    {
        $ulCache = $this->cache->getSegment('hotForumTopics' . $days);
        $ul = $ulCache->getData();
        if (!$ul) {
            $arr = [];
            $start = $this->request->timestamp - $days * self::ONE_DAY;

            foreach ((new Node())->getHotForumTopics(self::$city->tidForum, $count, $start) as $i => $n) {
                $arr[] = [
                    'after' => $i + 1,
                    'uri' => '/node/' . $n['nid'],
                    'text' => $n['title']
                ];
            }

            foreach ($ads as $n) {
                $arr[] = [
                    'time' => (int) $n['exp_time'],
                    'method' => 'toDate',
                    'uri' => '/node/' . $n['nid'],
                    'text' => '🏷️ ' . $n['title']
                ];
            }

            $ul = $this->linkNodeList($arr, 0, $ulCache);

            if (self::$city->id === City::HOUSTON) {
                $this->getCacheEvent('YellowPageUpdate')->addListener($ulCache);
            }
        }

        return $ul;
    }


    private function getLatestForumTopicReplies(int $count, array $ads = []): Template
    {
        $ulCache = $this->cache->getSegment('latestForumTopicReplies');
        $ul = $ulCache->getData();
        if (!$ul) {
            $arr = [];

            $nodes = (new Node())->getLatestForumTopicReplies(self::$city->tidForum, $count);
            $lastModifiedTime = $nodes ? (int) $nodes[0]['create_time'] : 0;
            foreach ($nodes as $n) {
                $arr[] = [
                    'after' => $n['comment_count'],
                    'uri' => '/node/' . $n['nid'] . '?p=l#bottom',
                    'text' => $n['title']
                ];
            }

            foreach ($ads as $n) {
                $arr[] = [
                    'time' => (int) $n['exp_time'],
                    'method' => 'toDate',
                    'uri' => '/node/' . $n['nid'],
                    'text' => '🏷️ ' . $n['title']
                ];
            }

            $ul = $this->linkNodeList($arr, $lastModifiedTime, $ulCache);

            $this->getCacheEvent('ForumComment')->addListener($ulCache);
            if (self::$city->id === City::HOUSTON) {
                $this->getCacheEvent('YellowPageUpdate')->addListener($ulCache);
            }
        } else {
            $lastModifiedTime = $this->getNodeListTime((string) $ul);
        }
        $this->lastModifiedTime = max($this->lastModifiedTime, $lastModifiedTime);

        return $ul;
    }

    private function linkNodeList(array $arr, int $lastModifiedTime, SegmentCache $ulCache): Template
    {
        $ul = (new HomeItemlist())->setData($arr)->setLastModifiedTime($lastModifiedTime);

        $ulCache->setData($ul);
        foreach ($arr as $n) {
            $ulCache->addParent(preg_replace('/[?#].*/', '', $n['uri']));
        }

        return $ul;
    }

    private function getNodeListTime(string $str): int
    {
        // extract time from home_itemlist template
        return preg_match('/^<!-- ([0-9]*) -->/', $str, $matches) ? (int) $matches[1] : 0;
    }
}
