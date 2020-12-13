<?php

declare(strict_types=1);

namespace site\handler\app;

use Exception;
use lzx\cache\CacheHandler;
use lzx\exception\NotFound;
use lzx\html\Template;
use site\Controller;

class Handler extends Controller
{
    public function run(): void
    {
        if (!$this->args || $this->args[0] !== 'frontend_app') {
            throw new NotFound();
        }

        $this->html->setContent(
            Template::fromStr(
                implode(
                    '',
                    array_map(function ($i) {
                        return (string) CacheHandler::getInstance()->createCache($i)->getData();
                    }, [
                        'latestForumTopics',
                        'latestForumTopicReplies',
                        'hotForumTopics7',
                        'hotForumTopics30'
                    ])
                )
            )
        );
    }
}
