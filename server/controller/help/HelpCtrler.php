<?php

namespace site\controller\help;

use site\controller\Help;

class HelpCtrler extends Help
{

    public function run()
    {
        $this->html->var['content'] = '帮助文档建立中 ... ...<br />'
            . '<ol><li>发布活动要先在论坛的活动版发个活动贴，然后在活动贴中点击“发布为活动”发布，就可以在首页和活动页面显示啦</li>'
            . '<li><a href="/node/5537">如何格式化帖子正文文本 (论坛BBCode详解)</a></li>'
            . '<li><a href="/node/80">如何在帖子中插入图片</a></li><li><a href="/node/5535">如何在帖子中插入YouTube或土豆视频</a></li>'
            . '<li>首页轮换图片是从宽度等于600px，高度不小于300px的15张最新的附件图片中随机选取10张播放</li></ol>';
    }

}

//__END_OF_FILE__
