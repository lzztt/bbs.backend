/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {

    $('#coin-slider').coinslider({
        effect: 'straight',
        spw: 1,
        sph: 1
    });

    var ebox = $('#extendbox');
    var activity = $('#activity')
    var name = activity.text();
    var link = activity.attr('href');

    var attendForm = '<div class="spacer"></div>\
    <div id="extendbox_content" class="extendbox_inner"><a class="closeButton closeExtendBox" href="#">关闭</a>\
        <form action="/single/attend" method="post" accept-charset="UTF-8">\
            <div><h3>' + name + ' 单身聚会报名</h3>活动内容或去附近公园春游野餐，或去喝下午茶玩游戏，然后晚餐，晚餐后K歌打牌或其他，会报名后稍候email通知<br />时间为下午3:30到8:30<br />报名后，现场check in 可得到 Email contact list. \
            <a href="' + link + '">活动讨论贴</a><br />\
            以下带星号(<span class="form_required" title="此项必填。">*</span>)选项为必填选项，报名提交后会收到email确认活动信息</div>\
            <table>\
                <tr>\
                    <td class="form_item">姓名 / 昵称 <span class="form_required" title="此项必填。">*</span></td>\
                    <td>\
                        <input type="text" name="name" value="" maxlength="100" />\
                    </td>\
                </tr>\
                <tr>\
                    <td class="form_item">性别 <span class="form_required" title="此项必填。">*</span></td>\
                    <td>\
                        <input type="radio" name="sex" value="1" /> 男\
                        <input type="radio" name="sex" value="0" /> 女\
                    </td >\
                </tr>\
                <tr>\
                    <td class="form_item">年龄 <span class="form_required" title="此项必填。">*</span></td>\
                    <td>\
                        <input type="text" name="age" value="" maxlength="100" />\
                    </td>\
                </tr>\
                <tr>\
                    <td class="form_item">E-Mail <span class="form_required" title="此项必填。">*</span></td>\
                    <td>\
                        <input type="text" name="email" value="" maxlength="100" />\
                    </td>\
                </tr>\
                <tr>\
                    <td class="form_item">电话</td>\
                    <td>\
                        <input type="text" name="phone" value="" maxlength="100" />\
                    </td>\
                </tr>\
                <tr><td> </td><td> </td></tr>\
                <tr>\
                    <td class="form_item">留言时匿名</td>\
                    <td>\
                        <input type="radio" name="anonymous" value="1" /> 是\
                        <input type="radio" name="anonymous" value="0" checked="checked" /> 否\
                    </td >\
                </tr>\
                <tr>\
                    <td class="form_item">留言 / 自我介绍 / 活动建议<br/><span class="form_tip">留言将会同时发布为公共可见留言<br />若不想公开显示姓名或昵称<br />请选择"匿名"留言</span></td>\
                    <td>\
                        <textarea rows="9" cols="70" name="comment"></textarea>\
                    </td>\
                </tr>\
                <tr><td> </td>\
                    <td>\
                        <input class="submitForm" type="submit" value="提交" />\
                        <input class="closeExtendBox" type="reset" value="取消" />\
                    </td>\
                </tr>\
            </table>\
        </form>\
    </div>';

    var subscribeForm = '<div class="spacer"></div>\
    <div id="extendbox_content" class="extendbox_inner"><a class="closeButton closeExtendBox" href="#">关闭</a>\
        <form action="/single/subscribe" method="post" accept-charset="UTF-8">\
            <div>请填写您的联系信息，以便以后活动可以及时通知到您</div>\
            E-Mail <input type="text" name="email" value="" maxlength="100" />\
            <input class="submitForm" type="submit" value="关注" />\
            <input class="closeExtendBox" type="reset" value="取消" />\
        </form>\
    </div>';

    var commentForm = '<div class="spacer"></div>\
    <div id="extendbox_content" class="extendbox_inner"><a class="closeButton closeExtendBox" href="#">关闭</a>\
        <form action="/single/comment" method="post" accept-charset="UTF-8">\
            <div>以下带星号(<span class="form_required" title="此项必填。">*</span>)选项为必填选项</div>\
            <table>\
                <tr>\
                    <td class="form_item">姓名 / 昵称</td>\
                    <td>\
                        <input type="text" name="name" value="" maxlength="100" />\
                    </td>\
                </tr>\
                <tr>\
                    <td class="form_item">留言评论 <span class="form_required" title="此项必填。">*</span><br/><span class="form_tip">留言为公共可见，最少为5个字符<br />若希望"匿名"留言<br />请不要填写姓名或昵称</span></td>\
                    <td>\
                        <textarea rows="9" cols="70" name="comment"></textarea>\
                    </td>\
                </tr>\
                <tr><td> </td>\
                    <td>\
                        <input class="submitForm" type="submit" value="提交" />\
                        <input class="closeExtendBox" type="reset" value="取消" />\
                    </td>\
                </tr>\
            </table>\
        </form>\
    </div>';

    $('#attendButton').click(function(e) {
        e.preventDefault();

        ebox.html(attendForm);
    });

    $('#subscribeButton').click(function(e) {
        e.preventDefault();

        ebox.html(subscribeForm);
    });

    $('#commentButton').click(function(e) {
        e.preventDefault();

        ebox.html(commentForm);
    });

    $(".commentViewButton").live("click", function(e) {
        e.preventDefault();
        $.get("/single/comment/view", function(data) {
            ebox.html('<div class="spacer"></div><div id="extendbox_content" class="extendbox_inner"><a href="#" class="closeButton closeExtendBox">关闭</a></div>');
            $("#extendbox_content", ebox).append(data)
        })
    });

    $(".ageChartButton").live("click", function(e) {
        e.preventDefault();
        $.get("/single/chart", function(data) {
            ebox.html('<div class="spacer"></div><div id="extendbox_content" class="extendbox_inner"><a href="#" class="closeButton closeExtendBox">关闭</a></div>');
            $("#extendbox_content", ebox).append(data)
        })
    });

    $(".closeExtendBox").live("click", function(e) {
        e.preventDefault();
        ebox.html("")
    });

    $('.submitForm').live("click", function(e) {
        e.preventDefault();

        var form = $(this).closest("form");
        $.post(form.attr("action"), form.serialize(), function(data) {
            ebox.html('<div class="spacer"></div><div id="extendbox_content" class="extendbox_inner"><a href="#" class="closeButton closeExtendBox">关闭</a></div>');
            $("#extendbox_content", ebox).html(data);
        });
    });

});
