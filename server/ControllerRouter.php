<?php

//!!!
//!!!  do not edit, generated by script/build_route.sh
//!!!

namespace site;

use site\ControllerFactory;

/**
 * Description of ControllerRouter
 *
 * @author ikki
 */
class ControllerRouter extends ControllerFactory
{
    protected static $route = [
        'activity'                =>  'site\\handler\\activity\\Handler',
        'ad'                      =>  'site\\handler\\ad\\Handler',
        'api/ad'                  =>  'site\\handler\\api\\ad\\Handler',
        'api/adpayment'           =>  'site\\handler\\api\\adpayment\\Handler',
        'api/authentication'      =>  'site\\handler\\api\\authentication\\Handler',
        'api/bookmark'            =>  'site\\handler\\api\\bookmark\\Handler',
        'api/captcha'             =>  'site\\handler\\api\\captcha\\Handler',
        'api/file'                =>  'site\\handler\\api\\file\\Handler',
        'api/identificationcode'  =>  'site\\handler\\api\\identificationcode\\Handler',
        'api/message'             =>  'site\\handler\\api\\message\\Handler',
        'api/report'              =>  'site\\handler\\api\\report\\Handler',
        'api/stat'                =>  'site\\handler\\api\\stat\\Handler',
        'api/user'                =>  'site\\handler\\api\\user\\Handler',
        'api/viewcount'           =>  'site\\handler\\api\\viewcount\\Handler',
        'app'                     =>  'site\\handler\\app\\Handler',
        'comment/delete'          =>  'site\\handler\\comment\\delete\\Handler',
        'comment/edit'            =>  'site\\handler\\comment\\edit\\Handler',
        'forum'                   =>  'site\\handler\\forum\\Handler',
        'forum/node'              =>  'site\\handler\\forum\\node\\Handler',
        'help'                    =>  'site\\handler\\help\\Handler',
        'home'                    =>  'site\\handler\\home\\Handler',
        'node'                    =>  'site\\handler\\node\\Handler',
        'node/activity'           =>  'site\\handler\\node\\activity\\Handler',
        'node/bookmark'           =>  'site\\handler\\node\\bookmark\\Handler',
        'node/comment'            =>  'site\\handler\\node\\comment\\Handler',
        'node/delete'             =>  'site\\handler\\node\\delete\\Handler',
        'node/edit'               =>  'site\\handler\\node\\edit\\Handler',
        'node/tag'                =>  'site\\handler\\node\\tag\\Handler',
        'search'                  =>  'site\\handler\\search\\Handler',
        'single'                  =>  'site\\handler\\single\\Handler',
        'single/activities'       =>  'site\\handler\\single\\activities\\Handler',
        'single/ajax'             =>  'site\\handler\\single\\ajax\\Handler',
        'single/attendee'         =>  'site\\handler\\single\\attendee\\Handler',
        'single/checkin'          =>  'site\\handler\\single\\checkin\\Handler',
        'single/info'             =>  'site\\handler\\single\\info\\Handler',
        'single/login'            =>  'site\\handler\\single\\login\\Handler',
        'single/logout'           =>  'site\\handler\\single\\logout\\Handler',
        'term'                    =>  'site\\handler\\term\\Handler',
        'wedding'                 =>  'site\\handler\\wedding\\Handler',
        'wedding/add'             =>  'site\\handler\\wedding\\add\\Handler',
        'wedding/checkin'         =>  'site\\handler\\wedding\\checkin\\Handler',
        'wedding/edit'            =>  'site\\handler\\wedding\\edit\\Handler',
        'wedding/gift'            =>  'site\\handler\\wedding\\gift\\Handler',
        'wedding/join'            =>  'site\\handler\\wedding\\join\\Handler',
        'wedding/listall'         =>  'site\\handler\\wedding\\listall\\Handler',
        'wedding/login'           =>  'site\\handler\\wedding\\login\\Handler',
        'wedding/logout'          =>  'site\\handler\\wedding\\logout\\Handler',
        'yp'                      =>  'site\\handler\\yp\\Handler',
        'yp/join'                 =>  'site\\handler\\yp\\join\\Handler',
        'yp/node'                 =>  'site\\handler\\yp\\node\\Handler',
    ];
}
