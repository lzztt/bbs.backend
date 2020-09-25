<?php

use lzx\html\Template;

function (
  Template $breadcrumb,
  int $exampleTime
) {
?>

  <div id="content">
    <div id="content-area">
      <div class="breadcrumb"><?= $breadcrumb ?></div>

      <div style="margin-top: 10px;">
        请您填写活动时间，有效活动时间为未来60天之内，您也可以重复提交此申请来修改活动时间。<br />
        时间格式为<span style="background-color:orange; color:blue">24小时制</span>：
        <span style="background-color:orange; color:blue">MM/DD/YYYY HH:MM</span>，
        例如：<span style="background-color:orange; color:blue"><?= date('m/d/Y H:i', $exampleTime) ?></span><br />
        请修改下面的示例时间
      </div>
      <form id="privatemsg-new" method="post" accept-charset="UTF-8" action="">
        <div>
          <div id="edit-subject-wrapper" class="form-item">
            <label for="edit-subject">活动开始时间：</label>
            <input type="text" name="start_time" placeholder="<?= date('m/d/Y H:i', $exampleTime) ?>" size="60" maxlength="50" required="required" />
          </div>
          <div id="edit-subject-wrapper" class="form-item">
            <label for="edit-subject">活动结束时间：</label>
            <input type="text" name="end_time" placeholder="<?= date('m/d/Y H:i', $exampleTime + 7200) ?>" size="60" maxlength="50" required="required" />
          </div>
          <input type="submit" class="form-submit" value="提交申请" id="edit-submit" />
        </div>
      </form>
    </div>
  </div>

<?php
};
