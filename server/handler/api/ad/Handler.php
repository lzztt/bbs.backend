<?php

declare(strict_types=1);

namespace site\handler\api\ad;

use site\Service;
use site\dbobject\Ad;

class Handler extends Service
{
    /**
     * get ads
     * uri: /api/ad/name
     *      /api/ad
     */
    public function get(): void
    {
        $this->validateAdmin();

        $ad = new Ad();

        if ($this->args) {
            if ($this->args[0] == 'name') {
                $ad->order('expTime', false);
                $this->json($ad->getList('name'));
            }
        } else {
            $a_month_ago = $this->request->timestamp - 2592000;
            $this->json($ad->getAllAds($a_month_ago));
        }
    }

    /**
     * create add ad
     * uri: /api/ad[?action=post]
     * post: name=<name>&email=<email>&type_id=<type_id>
     */
    public function post(): void
    {
        $this->validateAdmin();

        $ad = new Ad();

        $ad->name = $this->request->data['name'];
        $ad->email = $this->request->data['email'];
        $ad->typeId = (int) $this->request->data['typeId'];
        $ad->expTime = $this->request->timestamp;
        $ad->add();

        $this->json(['id' => $ad->id, 'name' => $ad->name, 'email' => $ad->email]);
    }
}
