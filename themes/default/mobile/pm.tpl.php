<div id="content">
  <div id="content-area">
    <div class="breadcrumb"><?php echo $breadcrumb; ?></div>
    <h1 id="pm-subject" class="title"><?php echo $subject; ?></h1>
    <?php foreach ($msgs as $k => $m): ?>
      <div id="privatemsg-mid-2152" class="privatemsg-box-fb odd">
        <div style="background-color: pink; margin: 3px 0;">
            <a title="浏览用户信息" href="/user/<?php echo $m['uid']; ?>"><?php echo $m['username']; ?></a> <?php echo ($m['time']); ?>
        </div>
        <div class="right-column">
          <div class="message-body"><?php echo nl2br($m['body']); ?></div>

        </div>
        <div class="clear-both bottom-border"></div>
      </div>
    <?php endforeach; ?>

    <form id="privatemsg-new" method="post" accept-charset="UTF-8" action="<?php echo $form_handler; ?>">
      <div>
        <div>收信人: <a title="浏览用户信息" href="/user/<?php echo $replyToUID; ?>"><?php echo $replyToName; ?></a></div>
        <div id="edit-body-wrapper" class="form-item">
          <label for="edit-body">回复消息:</label>
          <textarea class="form-textarea resizable textarea-processed" id="edit-body" name="body" rows="6" cols="60" placeholder="最少5个字母或3个汉字" required="required"></textarea>
        </div>
        <input type="submit" class="form-submit" value="Send message" id="edit-submit"  />
        <input type="hidden" value="<?php echo $replyToUID; ?>" name="reply_to_uid" />
      </div>
    </form>
  </div>
</div>