<form enctype="multipart/form-data" id="bbcode_editor" class='v_user' method="post" accept-charset="UTF-8" action="<?php print $form_handler; ?>">
   <fieldset class="node_title" style='display: none;'>
      <label class='label' for="title">标题</label>
      <input type="text" name="title" value="<?php print $title; ?>" required="required" placeholder="最少5个字母或3个汉字">
   </fieldset>
   <fieldset>
      <label class='label' for="body">正文</label>
      <textarea name="body" required="required" placeholder="最少5个字母或3个汉字"></textarea>
   </fieldset>
   <fieldset>
      <label class='label'>文件附件</label>
      <input type="hidden" name='update_file' value='1'>
      <table id="ajax_file_list" style="display:none;">
         <thead><tr><th>图片名</th><th>BBCode</th><th>删除</th></tr></thead>
         <tbody></tbody>
      </table>
      <div>
         <label for="ajax_file_select">上传新附件</label>
         <input type="file" id="ajax_file_select" class="form-file" name="attachment[]" multiple="multiple"> <button class="ajax_file_upload">上传</button>
         <div class="description">分辨率大于 <em>600x960</em> 的图片将被调整尺寸。 文件最大上传大小为 <em>1 MB</em> 。只允许以下上传文件格式：<em>jpg jpeg gif png</em> 。 </div>
      </div>
   </fieldset>
   <fieldset><button>发布</button></fieldset>
</form>