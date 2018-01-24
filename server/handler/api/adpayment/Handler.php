<?php declare(strict_types=1);

namespace site\handler\api\adpayment;

use lzx\core\Mailer;
use lzx\exception\Forbidden;
use site\Service;
use site\dbobject\Ad;
use site\dbobject\AdPayment;

class Handler extends Service
{
    /**
     * get payments
     * uri: /api/adpayment
     */
    public function get(): void
    {
        if ($this->request->uid != 1) {
            throw new Forbidden();
        }

        $ad = new Ad();
        $a_month_ago = $this->request->timestamp - 2592000;
        $this->json($ad->getAllAdPayments($a_month_ago));
    }

    /**
     * create ad payment
     * uri: /api/adpayment[?action=post]
     * post: ad_id=<ad_id>&amount=<amount>&time=<time>&ad_time=<ad_time>&comment<comment>
     */
    public function post(): void
    {
        if ($this->request->uid != 1) {
            throw new Forbidden();
        }

        $ad = new Ad();
        $ap = new AdPayment();
        $ap->adId = $this->request->post['ad_id'];
        $ap->amount = $this->request->post['amount'];
        $ap->time = strtotime($this->request->post['time']);
        $ap->comment = $this->request->post['comment'];
        $ap->add();

        $ad->id = $ap->adId;
        $ad->load('name,email,typeId,expTime');
        if ($ad->expTime < $this->request->timestamp) {
            $exp_time = $this->request->post['time'];
        } else {
            $exp_time = date('m/d/Y', $ad->expTime);
        }
        $ad->expTime = strtotime($exp_time . ' +' . $this->request->post['ad_time'] . ' months');
        $ad->update('expTime');
        foreach (['latestYellowPages', '/'] as $key) {
            $this->getIndependentCache($key)->delete();
        }
        $this->sendConfirmationEmail($ad);

        $this->json(['adName' => $ad->name, 'amount' => $ap->amount, 'expTime' => $ad->expTime]);
    }

    private function sendConfirmationEmail(Ad $ad): void
    {
        $mailer = new Mailer('ad');
        $mailer->setTo($ad->email);
        $siteName = ucfirst(self::$city->uriName) . 'BBS';
        $type = $ad->typeId == 1 ? '电子黄页' : '页顶广告';
        $date = date('m/d/Y', $ad->expTime);
        $mailer->setSubject($ad->name . '在' . $siteName . '的' . $type . '有效日期更新至' . $date);
        $contents = [
            'name' => $ad->name,
            'type' => $type,
            'date' => $date,
            'sitename' => $siteName,
        ];
        $mailer->setBody((string) new Template('mail/adpayment', $contents));

        $mailer->setBcc($this->config->webmaster);
        $mailer->send();
    }
}
