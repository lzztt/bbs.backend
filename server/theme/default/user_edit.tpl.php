<?= $userLinks ?>
<form accept-charset="UTF-8" autocomplete="off" method="post" action="<?= $action ?>" enctype="multipart/form-data">
  <div class="form_element">
    <label data-help="您的虚拟头像。最大尺寸是 <em>120 x 120</em> 像素，最大大小为 <em>60</em> KB">用户头像</label>
    <input size="22" name="avatar" type="file"><img class="avatar" src="<?= $avatar ?>">
  </div>

  <div class="form_element">
    <label>微信</label>
    <input size="22" name="wechat" type="text" value="<?= $wechat ?>">
  </div>
  <div class="form_element">
    <label>QQ</label>
    <input size="22" name="qq" type="text" value="<?= $qq ?>">
  </div>
  <div class="form_element">
    <label>个人网站</label>
    <input size="22" name="website" type="text" value="<?= $website ?>">
  </div>

  <div class="form_element">
    <label data-help="不会公开显示">姓名</label>
    名 <input size="10" name="firstname" type="text" value="<?= $firstname ?>">
    姓 <input size="10" name="lastname" type="text" value="<?= $lastname ?>">
  </div>
  <div class="form_element">
    <label>性别</label>
    <select class="form_element" name="sex">
      <option value="null">未选择</option>
      <option value="0">女</option>
      <option value="1">男</option>
    </select>
  </div>
</div>
<div class="form_element">
  <label data-help="用于计算年龄，出生年不会公开显示">生日</label>
  月(mm) <input size="10" name="bmonth" type="text" value="<?= $bmonth ?>">
  日(dd) <input size="10" name="bday" type="text" value="<?= $bday ?>">
  年(yyyy) <input size="10" name="byear" type="text" value="<?= $byear ?>">
</div>
<div class="form_element">
  <label>职业</label><input size="22" name="occupation" type="text" value="<?= $occupation ?>">
</div>
<div class="form_element">
  <label>兴趣爱好</label><input size="22" name="interests" type="text" value="<?= $interests ?>">
</div>
<div class="form_element">
  <label>自我介绍</label><textarea rows="5" cols="50" name="aboutme"><?= $aboutme ?></textarea>
</div>

<div class="form_element"><button type="submit">保存</button>
</form>