<?php

declare(strict_types=1);

namespace site\handler\api\adpayment;

use lzx\core\Mailer;
use site\Service;
use site\dbobject\Ad;
use site\dbobject\AdPayment;
use site\gen\theme\roselife\mail\Adpayment as RoselifeAdpayment;

class Handler extends Service
{
    /**
     * get payments
     * uri: /api/adpayment
     */
    public function get(): void
    {
        $this->validateAdmin();

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
        $this->validateAdmin();

        $ad = new Ad();
        $ap = new AdPayment();
        $ap->adId = (int) $this->request->data['adId'];
        $ap->amount = (int) $this->request->data['amount'];
        $ap->time = strtotime($this->request->data['time']);
        $ap->comment = $this->request->data['comment'];
        $ap->add();

        $ad->id = $ap->adId;
        $ad->load('name,email,typeId,expTime');
        if ($ad->expTime < $this->request->timestamp) {
            $exp_time = $this->request->data['time'];
        } else {
            $exp_time = date('Y-m-d', $ad->expTime);
        }
        $ad->expTime = strtotime($exp_time . ' +' . $this->request->data['adTime'] . ' months');
        $ad->update('expTime');
        $this->getCacheEvent('YellowPageUpdate')->trigger();
        $this->sendConfirmationEmail($ad);

        $this->json(['adName' => $ad->name, 'amount' => $ap->amount, 'expTime' => $ad->expTime]);
    }

    private function sendConfirmationEmail(Ad $ad): void
    {
        $mailer = new Mailer('ad');
        $mailer->setTo($ad->email);
        $siteName = $this->getSiteName();
        $type = $ad->typeId == 1 ? '电子黄页' : '页顶广告';
        $date = date('m/d/Y', $ad->expTime);
        $mailer->setSubject($ad->name . '在' . $siteName . '的' . $type . '有效日期更新至' . $date);
        $mailer->setBody(
            (string) (new RoselifeAdpayment())
                ->setName($ad->name)
                ->setType($type)
                ->setDate($date)
                ->setSitename($siteName)
        );

        $mailer->setBcc($this->config->webmaster);
        $mailer->send();
    }
}
