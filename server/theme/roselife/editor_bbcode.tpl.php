<form enctype="multipart/form-data" id="bbcode_editor" class='v_user' method="post" accept-charset="UTF-8" action="<?= $form_handler ?>">
  <fieldset class="node_title"<?php if (!$displayTitle): ?> style='display: none;'<?php endif ?>>
    <label class='label' for="title">标题</label>
    <input type="text" name="title" value="<?= $title ?>" placeholder="最少5个字母或3个汉字">
  </fieldset>
  <fieldset>
    <label class='label' for="body">正文</label>
    <textarea name="body" required placeholder="最少5个字母或3个汉字"></textarea>
  </fieldset>
  <?php if ($hasFile): ?>
    <fieldset>
      <label class='label'>文件附件</label>
      <input type="hidden" name='update_file' value='1'>
      <table id="file_list" style="display:none;">
        <thead><tr><th>图片名</th><th>BBCode</th><th>删除</th></tr></thead>
        <tbody></tbody>
      </table>
      <div>
        <label for="ajax_file_select">上传新附件</label>
        <input type="file" id="file_select" class="form-file" name="attachment[]" multiple="multiple"><button type="button" id="file_upload">上传</button> <button type="button" id="file_clear">取消选中图片</button>
        <div class="description">分辨率大于 <em>600x960</em> 的图片将被调整尺寸。 文件最大上传大小为 <em>1 MB</em> 。只允许以下上传文件格式：<em>jpg jpeg gif png</em> 。 </div>
      </div>
    </fieldset>
  <?php endif ?>
  <fieldset><button type="submit">发布</button></fieldset>
</form>