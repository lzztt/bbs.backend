<form enctype="multipart/form-data" id="bbcode_editor" class='v_user' method="post" accept-charset="UTF-8" action="<?= $form_handler ?>">
  <fieldset class="node_title"<?php if (empty($displayTitle)): ?> style='display: none;'<?php endif ?>>
    <label class='label' for="title">标题</label>
    <input type="text" name="title" value="<?= empty($title) ? '' : $title ?>" placeholder="最少5个字母或3个汉字">
  </fieldset>
  <fieldset>
    <label class='label' for="body">正文</label>
    <textarea name="body" required placeholder="最少5个字母或3个汉字"></textarea>
  </fieldset>
  <?php if (!empty($hasFile)): ?>
    <fieldset>
      <label class='label'>文件附件</label>
      <input type="hidden" name='update_file' value='1'>
      <div id="file_list"></div>
      <template>
        <figure>
          <img src="" width="200">
          <figcaption width="200">
            <input disabled type="text" value="" name="file_id[]" hidden>
            <label>名称</label><input disabled type="text" value="" size=15 name="file_name[]"><br>
            <label>代码</label><input disabled type="text" value="" size=15 name="file_code[]"><br>
            <button type="button">删除</button>
          </figcaption>
        </figure>
      </template>
      <div>
        <label for="ajax_file_select">上传图片</label>
        <input type="file" id="file_select">
        <div class="description">宽度大于 <em>600</em> 像素的图片将被调整尺寸。</div>
      </div>
    </fieldset>
  <?php endif ?>
  <fieldset><button type="submit">发布</button></fieldset>
</form>