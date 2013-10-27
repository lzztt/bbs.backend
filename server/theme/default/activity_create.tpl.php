<div id="content">
  <div id="content-area">
    <div class="breadcrumb"><?php echo $breadcrumb; ?></div>

    <div style="margin-top: 10px;">
      请您填写活动时间，有效活动时间为未来60天之内，您也可以重复提交此申请来修改活动时间。<br />
      时间格式为<span style="background-color:orange; color:blue">24小时制</span>：<span style="background-color:orange; color:blue">MM/DD/YYYY HH:MM</span>，例如：<span style="background-color:orange; color:blue"><?php echo date('m/d/Y H:i', $exampleDate); ?></span><br />
      请修改下面的示例时间
    </div>
    <form id="privatemsg-new" method="post" accept-charset="UTF-8" action="<?php echo $form_handler; ?>">
      <div>
        <div id="edit-subject-wrapper" class="form-item">
          <label for="edit-subject">活动开始时间：</label>
          <input type="text" name="start_time" id="edit-subject" placeholder="<?php echo date('m/d/Y H:i', $newDate); ?>" size="60" maxlength="50" required="required" />
        </div>
        <div id="edit-subject-wrapper" class="form-item">
          <label for="edit-subject">活动结束时间：</label>
          <input type="text" name="end_time" id="edit-subject" placeholder="<?php echo date('m/d/Y H:i', $newDate + 7200); ?>" size="60" maxlength="50" required="required" />
        </div>
        <input type="submit" class="form-submit" value="提交申请" id="edit-submit"  />
      </div>
    </form>
  </div>
</div>