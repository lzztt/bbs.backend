<ul class="tabs">
   <li><a href="/user/display">用户首页</a></li>
   <li><a href="/user/pm">站内短信</a></li>
   <li class="active"><a href="/user/edit">编辑个人资料</a></li>
   <li><a href="/password/change">更改密码</a></li>
</ul>
<form accept-charset="UTF-8" autocomplete="off" method="post" action="/user/edit/<?php print $uid; ?>" enctype="multipart/form-data">
      <div class="form_element">
         <div class="element_label"><label>用户头像</label><span class="element_help" title="您的虚拟头像。最大尺寸是 <em>120 x 120</em> 像素，最大大小为 <em>60</em> KB"> ? </span></div>
         <div class="element_input"><input size="22" name="avatar" type="file"><img class="avatar" src="<?php print $avatar; ?>"></div>
      </div>

      <div class="form_element">
         <div class="element_label"><label>微信</label></div>
         <div class="element_input"><input size="22" name="msn" type="text" value="<?php print $wechat; ?>"></div>
      </div>
      <div class="form_element">
         <div class="element_label"><label>QQ</label></div>
         <div class="element_input"><input size="22" name="qq" type="text" value="<?php print $qq; ?>"></div>
      </div>
      <div class="form_element">
         <div class="element_label"><label>个人网站</label></div>
         <div class="element_input"><input size="22" name="website" type="text" value="<?php print $website; ?>"></div>
      </div>

      <div class="form_element">
         <div class="element_label"><label>姓名</label><span class="element_help" title="不会公开显示"> ? </span></div>
         <div class="element_input">
            <div class="form_element" style="display:inline"><label>名</label><input size="10" name="firstname" type="text" value="<?php print $firstname; ?>"></div>
            <div class="form_element" style="display:inline"><label>姓</label><input size="10" name="lastname" type="text" value="<?php print $lastname; ?>"></div>
         </div>
      </div>
      <div class="form_element">
         <div class="element_label"><label>性别</label></div>
         <div class="element_input">
            <select class="form_element" name="sex">
               <option value="null">未选择</option>
               <option value="0">女</option>
               <option value="1">男</option>
            </select>
         </div>
      </div>
      <div class="form_element">
         <div class="element_label">
            <label>生日</label><span class="element_help" title="用于计算年龄，出生年不会公开显示"> ? </span>
         </div>
         <div class="element_input">
            <div class="form_element" style="display:inline"><label>月(mm)</label><input size="10" name="bmonth" type="text" value="<?php print $bmonth; ?>"></div>
            <div class="form_element" style="display:inline"><label>日(dd)</label><input size="10" name="bday" type="text" value="<?php print $bday; ?>"></div>
            <div class="form_element" style="display:inline"><label>年(yyyy)</label><input size="10" name="byear" type="text" value="<?php print $byear; ?>"></div>
         </div>
      </div>
      <div class="form_element">
         <div class="element_label"><label>职业</label></div><div class="element_input"><input size="22" name="occupation" type="text" value="<?php print $occupation; ?>"></div>
      </div>
      <div class="form_element">
         <div class="element_label"><label>兴趣爱好</label></div>
         <div class="element_input"><input size="22" name="interests" type="text" value="<?php print $interests; ?>"></div>
      </div>
      <div class="form_element">
         <div class="element_label"><label>自我介绍</label></div>
         <div class="element_input"><textarea rows="5" cols="50" name="aboutme"><?php print $aboutme; ?></textarea></div>
      </div>

   <div class="form_element"><div class="element_input"><button type="submit">保存</button></div></div>
</form>