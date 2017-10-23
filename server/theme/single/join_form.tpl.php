<div>
  <h3><?php print \date( 'n月j日 ', $activity['time'] ) . $activity['name']; ?> 报名</h3>
  报名后，现场check in 可得到 Email contact list.
  <a href="/node/<?php print $activity['nid']; ?>">活动讨论贴</a><br>
  以下带星号(<span class="form_required" title="此项必填。">*</span>)选项为必填选项，报名提交后会收到email确认活动信息
</div>
<form id="ajax-attend" action="/single/ajax/attend" method="post" accept-charset="UTF-8">
  <input type="hidden" name="aid" value="<?php print $activity['id']; ?>">
  <div class='form-row'>
    <label>姓名 / 昵称 *</label>
    <div class='input'>
      <input type="text" name="name" value="" maxlength="100">
    </div>
  </div>
  <div class='form-row'>
    <label>性别 *</label>
    <div class='input'>
      <input type="radio" name="sex" value="1"> 男 <input type="radio" name="sex" value="0"> 女
    </div>
  </div>
  <div class='form-row'>
    <label>年龄 *</label>
    <div class='input'>
      <input type="number" name="age" value="" maxlength="100">
    </div>
  </div>
  <div class='form-row'>
    <label>E-Mail *</label>
    <div class='input'>
      <input type="email" name="email" value="" maxlength="100">
    </div>
  </div>
  <div class='form-row'>
    <label>电话</label>
    <div class='input'>
      <input type="text" name="phone" value="" maxlength="100">
    </div>
  </div>
  <div class='form-row'>
    <label>留言时匿名</label>
    <div class='input'>
      <input type="radio" name="anonymous" value="1"> 是 <input type="radio" name="anonymous" value="0" checked="checked"> 否
    </div>
  </div>
  <div class='form-row'>
    <label data-help='留言将会同时发布为公共可见留言<br>若不想公开显示姓名或昵称<br>请选择"匿名"留言'>留言评论</label>
    <div class='input'>
      <textarea rows="6" name="comment"></textarea>
    </div>
  </div>
  <div class='form-row'>
    <label></label>
    <div class='input'>
      <button type="submit">提交</button>
      <button type="reset">取消</button>
    </div>
  </div>
</form>