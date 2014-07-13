<div id="editor-div" data-urole='<?php print $urole_user; ?>'>
  <form enctype="multipart/form-data" id="editor-form" method="post" accept-charset="UTF-8" action="<?php print $form_handler; ?>">
    <span id='node-title' style='display: none'><?php print $title; ?></span>
    <div id="edit-title" class="form-item" style="display:<?php print ($show_title ? 'block' : 'none'); ?>;">
      <label for="title">标题:<span title="此项必填。" class="form-required">*</span></label>
      <input type="text" class="form-text required" value="<?php print $title; ?>" size="60" id="title" name="title" maxlength="50" placeholder="最少5个字母或3个汉字" />
    </div>
    <div id="edit-body" class="form-item">
      <label for="BBCodeEditor">正文:<span title="此项必填。" class="form-required">*</span></label>
      <textarea rows="20" cols="60" name="body" id="BBCodeEditor" class="text-full form-textarea" required="required" placeholder="最少5个字母或3个汉字"></textarea>

      <div class="attachments">
        <fieldset class=" collapsible collapsed">
          <legend class="collapse-processed"><a href="#">文件附件</a></legend>
          <div class="fieldset-wrapper">

            <input type="hidden" name='update_file' value='1' />
            <table id="ajax-file-list" style="display:none;">
              <thead class="tableHeader-processed">
                <tr><th>文字描述</th><th>BBCode</th><th>删除</th></tr>
              </thead>
              <tbody>
              </tbody>
            </table>

            <div id="attach-wrapper">
              <label for="ajax-file-select">上传新附件:</label>
              <input type="file" id="ajax-file-select" class="form-file" name="attachment[]" multiple="multiple" /> <input type="button" id="ajax-file-upload" value="上传" />

              <div class="description">分辨率大于 <em>600x960</em> 的图片将被调整尺寸。 文件最大上传大小为 <em>1 MB</em> 。只允许以下上传文件格式：<em>jpg jpeg gif png</em> 。 </div>

            </div>
          </div>
        </fieldset>
      </div>
      <input type="submit" class="form-submit" value="Submit" id="edit-submit"  />
    </div>
  </form>
</div>