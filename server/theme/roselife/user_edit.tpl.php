<?php print $userLinks; ?>
<form accept-charset="UTF-8" autocomplete="off" method="post" action="<?php print $action; ?>" enctype="multipart/form-data">
   <fieldset>
      <label class='label' data-help="您的虚拟头像。最大尺寸是 <em>120 x 120</em> 像素，最大大小为 <em>60</em> KB">用户头像</label>
      <input name="avatar" type="file"><img class="avatar" src="<?php print $avatar; ?>">
   </fieldset>
   <fieldset>
      <label class='label'>微信</label>
      <input name="wechat" type="text" value="<?php print $wechat; ?>">
   </fieldset>

   <fieldset>
      <label class='label'>QQ</label>
      <input name="qq" type="text" value="<?php print $qq; ?>">
   </fieldset>
   <fieldset>
      <label class='label'>个人网站</label>
      <input name="website" type="url" value="<?php print $website; ?>">
   </fieldset>
   <fieldset>
      <label class='label' data-help="不会公开显示">姓名</label>
      名 <input class='name' name="firstname"  type="text" value="<?php print $firstname; ?>">
      姓 <input class='name' name="lastname" type="text" value="<?php print $lastname; ?>">
   </fieldset>
   <fieldset>
      <label class='label'>性别</label>
      <select name="sex">
         <option value="null">未选择</option>
         <option value="0" <?php if( $sex === 0 ): print 'selected="selected"'; endif; ?>>女</option>
         <option value="1" <?php if( $sex === 1 ): print 'selected="selected"'; endif; ?>>男</option>
      </select>
   </fieldset>
   <fieldset>
      <label class='label' data-help="用于计算年龄，出生年不会公开显示">生日</label>
      <input name="birthday" type="date" value="<?php print $birthday; ?>">
   </fieldset>
   <fieldset>
      <label class='label'>职业</label><input name="occupation" type="text" value="<?php print $occupation; ?>">
   </fieldset>
   <fieldset>
      <label class='label'>兴趣爱好</label><input name="interests" type="text" value="<?php print $interests; ?>">
   </fieldset>
   <fieldset>
      <label class='label'>自我介绍</label><textarea name="aboutme"><?php print $aboutme; ?></textarea>
   </fieldset>

   <fieldset><button type="submit">保存</button></fieldset>
</form>