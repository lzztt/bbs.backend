<div id="content">
  <div id="content-area">
    <form action="<?php echo $form_handler; ?>" accept-charset="UTF-8" method="post" id="user-login">
      <div>
        <div class="form-item" id="username-wrapper">
          <label for="username">用户名:<span class="form-required" title="此项必填。">*</span></label>
          <input type="text" maxlength="50" name="username" id="username" size="60" value="" class="form-text required" required="required" autofocus="autofocus" />
          <div class="description">输入您在 缤纷休斯顿 华人论坛 的用户名。</div>
        </div>
        <div class="form-item" id="password-wrapper">
          <label for="password">密码:<span class="form-required" title="此项必填。">*</span></label>
          <input type="password" name="password" id="password" maxlength="128" size="60" class="form-text required" required="required" />
          <div class="description">输入与您用户名相匹配的密码。</div>
        </div>
        <input type="submit"  id="edit-submit" value="登录" class="form-submit" />
      </div>
    </form>
  </div>
</div>