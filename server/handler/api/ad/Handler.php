<?php

namespace site\handler\api\ad;

use site\Service;
use site\dbobject\AD;

class Handler extends Service
{
    /**
     * get ads
     * uri: /api/ad/name
     *        /api/ad
     */
    public function get()
    {
        if ($this->request->uid != 1) {
            $this->forbidden();
        }

        $ad = new AD();

        if ($this->args) {
            if ($this->args[0] == 'name') {
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
    public function post()
    {
        if ($this->request->uid != 1) {
            $this->forbidden();
        }

        $ad = new AD();

        $ad->name = $this->request->post['name'];
        $ad->email = $this->request->post['email'];
        $ad->typeID = $this->request->post['type_id'];
        $ad->expTime = $this->request->timestamp;
        $ad->add();

        $this->json(['id' => $ad->id, 'name' => $ad->name, 'email' => $ad->email]);
    }
}
