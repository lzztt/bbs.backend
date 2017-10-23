<form method="post" action="/wedding/edit" name="edit" autocomplete="on">
  <input type="hidden" name='id' value='<?php print $id; ?>' />
  <div>
    <label for="name" accesskey="U">姓名</label>
    <input name="name" type="text" id="name" value="<?php print $name; ?>" placeholder="中文姓名" required="required" />
  </div>

  <div>
    <label for="comment">人数备注</label>
    <input name="comment" type="text" id="comment" value="<?php print $comment; ?>" placeholder="人数备注" />
  </div>

  <div>
    <label for="gift">礼品</label>
    <input name="gift" type="text" id="gift" value="<?php print $gift; ?>" placeholder="新婚礼品" />
  </div>

  <div>
    <label for="value">礼品价值</label>
    <input name="value" type="text" id="value" value="<?php print $value; ?>" placeholder="礼品价值" />
  </div>

  <div>
    <input type="submit" class="submit" id="submit" value="保存更新" />
  </div>
</form>