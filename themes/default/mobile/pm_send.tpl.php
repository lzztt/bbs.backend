<div id="content-area">
  <div class="breadcrumb"><?php echo $breadcrumb; ?></div>

  <form id="privatemsg-new" method="post" accept-charset="UTF-8" action="<?php echo $form_handler; ?>">
    <div>
      <div>收信人: <a title="浏览用户信息" href="/user/<?php echo $toUID; ?>"><?php echo $toName; ?></a></div>
      <div id="edit-subject-wrapper" class="form-item">
        <label for="edit-subject">发送消息:</label>
        <input type="text" name="subject" id="edit-subject" size="60" maxlength="50" placeholder="最少3个字母或2个汉字" required="required" autofocus="autofocus" />
      </div>
      <div id="edit-body-wrapper" class="form-item">
        <label for="edit-body">发送消息:</label>
        <textarea class="form-textarea resizable textarea-processed" id="edit-body" name="body" rows="6" cols="60" placeholder="最少5个字母或3个汉字" required="required"></textarea>
      </div>
      <input type="submit" class="form-submit" value="Send message" id="edit-submit"  />
      <input type="hidden" value="<?php echo $toName; ?>" name="to_name" />
    </div>
  </form>
</div>