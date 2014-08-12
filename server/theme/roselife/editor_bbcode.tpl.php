<form enctype="multipart/form-data" id="editor-form" class='bbcode_editor urole_user' method="post" accept-charset="UTF-8" action="<?php print $form_handler; ?>">
   <label for="title">标题</label>
   <input type="text" name="title" value="<?php print $title; ?>" required="required" placeholder="最少5个字母或3个汉字">
   <textarea name="body" required="required" placeholder="最少5个字母或3个汉字"></textarea>

   <fieldset class="attachments">
      <legend>文件附件</legend>
      <input type="hidden" name='update_file' value='1'>
      <table id="ajax-file-list" style="display:none;">
         <thead><tr><th>文字描述</th><th>BBCode</th><th>删除</th></tr></thead>
         <tbody></tbody>
      </table>
      <div>
         <label for="ajax-file-select">上传新附件</label>
         <input type="file" id="ajax-file-select" class="form-file" name="attachment[]" multiple="multiple"> <button class="ajax-file-upload">上传</button>
         <div class="description">分辨率大于 <em>600x960</em> 的图片将被调整尺寸。 文件最大上传大小为 <em>1 MB</em> 。只允许以下上传文件格式：<em>jpg jpeg gif png</em> 。 </div>
      </div>
   </fieldset>
   <button>发布</button>
</form>