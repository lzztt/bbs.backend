<div id="activity">
  <div>
    <h3>宁静夏日  七夕单身聚会信息收集</h3>
  </div>
  <form action="<?= $action ?>" method="post" accept-charset="UTF-8">
    <input type="hidden" name="uid" value="<?= $uid ?>">
    <div class='form-row'>
      <label>问题1</label>
      <div class='input'>
        <input type="text" name="question[]" value="" maxlength="100">
      </div>
    </div>
    <div class='form-row'>
      <label>问题2</label>
      <div class='input'>
        <input type="text" name="question[]" value="" maxlength="100">
      </div>
    </div>
    <div class='form-row'>
      <label>问题3</label>
      <div class='input'>
        <input type="text" name="question[]" value="" maxlength="100">
      </div>
    </div>
    <div class='form-row'>
      <label>简要自我介绍</label>
      <div class='input'>
        <textarea rows="6" name="info"></textarea>
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
</div>