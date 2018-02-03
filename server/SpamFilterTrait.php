<?php declare(strict_types=1);

namespace site;

use Exception;
use site\dbobject\SpamWord;
use site\dbobject\User;

trait SpamFilterTrait
{

    protected function validatePost(): void
    {
        $user = new User($this->request->uid, 'createTime,status');

        if ($user->status != 1) {
            throw new Exception('This user account cannot post message.');
        }

        $creationDays = (int) (($this->request->timestamp - $user->createTime) / 86400);
        if ($creationDays < 30) {
            $spamwords = (new SpamWord())->getList();
            
            if (array_key_exists('title', $this->request->data)) {
                $this->checkTitle($this->request->data['title'], $spamwords);
            }

            $this->checkBody($this->request->data['body'], $spamwords);

            if ($creationDays < 10) {
                $this->checkPostCounts($user, $creationDays);
            }
        }
    }

    protected function checkTitle(string $title, array $spamwords): void
    {
        $cleanTitle = self::cleanText($title, array_column(
            array_filter($spamwords, function (array $record): bool {
                return (bool) $record['title'];
            }),
            'word'
        ));

        if ($title && mb_strlen($title) - mb_strlen($cleanTitle) > 4) {
            throw new Exception('Title is not valid!');
        }
    }

    protected function checkBody(string $body, array $spamwords): void
    {
        $cleanBody = self::cleanText($body, array_column($spamwords, 'word'));

        $bodyLen = mb_strlen($body);
        if ($bodyLen > 35 && ($bodyLen - mb_strlen($cleanBody)) / $bodyLen > 0.4) {
            throw new Exception('Body text is not valid!');
        }
    }
    
    private static function cleanText(string $text, array $spamwords): string
    {
        $cleanText = preg_replace('#[^\p{Nd}\p{Han}\p{Latin}\s$/]+#u', '', $text);

        foreach ($spamwords as $w) {
            if (mb_strpos($cleanText, $w) !== false) {
                $this->deleteSpammer();
                throw new Exception('User is blocked! You cannot post any message!');
            }
        }
        
        return $cleanText;
    }
    
    protected function checkPostCounts(User $user, int $creationDays): void
    {
        $geo = geoip_record_by_name($this->request->ip);
        if ($geo && $geo['city'] === 'Nanning') {
            $this->deleteSpammer();
            throw new Exception('User is blocked! You cannot post any message!');
        }

        if (!$geo || $geo['region'] != 'TX') {
            $postCount = (int) array_pop(array_pop($user->call('get_user_post_count(' . $user->id . ')')));
            if ($postCount >= $creationDays) {
                throw new Exception('Quota Limit Exceeded! You can only post no more than ' . $creationDays . ' messages up to now. Please wait one day to get more quota.');
            }
        }
    }

    protected function deleteSpammer(): void
    {
        $this->logger->info('SPAMMER DELETED: uid=' . $this->request->uid);
        $this->deleteUser($this->request->uid);

        $u = new User();
        $u->lastAccessIp = inet_pton($this->request->ip);
        $u->status = 1;
        $users = $u->getList('createTime');

        $deleteAll = true;
        foreach ($users as $u) {
            if ($this->request->timestamp - (int) $u['createTime'] > 2592000) {
                $deleteAll = false;
                break;
            }
        }

        if ($deleteAll) {
            $this->logger->info('SPAMMER DELETED (IP=' . $this->request->ip . '): uid=' . implode(',', array_column($users, 'id')));
            foreach ($users as $u) {
                $this->deleteUser((int) $u['id']);
            }
        }
    }
}
