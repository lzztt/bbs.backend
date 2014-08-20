<form enctype="multipart/form-data" id="bbcode_editor" class='v_user_superadm' method="post" accept-charset="UTF-8" action="<?php print $form_handler; ?>">
   <fieldset>
      <label class='label' for="title">名称:</label>
      <input type="text" name="title" value="<?php print $title; ?>" required="required" placeholder="最少5个字母或3个汉字">
   </fieldset>
   <fieldset>
      <label class='label' for="address">地址:</label>
      <input type="text" value="<?php print $address; ?>" name="address" required="required" />
   </fieldset>
   <fieldset>
      <label class='label' for="phone">电话:</label>
      <input type="tel" value="<?php print $phone; ?>" name="phone" required="required" />
   </fieldset>
   <fieldset>
      <label class='label' for="email">电子邮箱:</label>
      <input type="email" value="<?php print $email; ?>" name="email" />
   </fieldset>
   <fieldset>
      <label class='label' for="website">网站:</label>
      <input type="url" value="<?php print $website; ?>" name="website" />
   </fieldset>
   <fieldset>
      <label class='label' for="body">内容介绍</label>
      <textarea name="body" required="required" placeholder="最少5个字母或3个汉字"><?php print $body; ?></textarea>
   </fieldset>
   <fieldset>
      <label class='label'>文件附件</label>
      <input type="hidden" name='update_file' value='1'>
      <table id="file_list" <?php if (!\is_array($files) || \sizeof($files) < 1): ?> style="display:none;" <?php endif; ?>>
         <thead><tr><th>图片名</th><th>BBCode</th><th>删除</th></tr></thead>
         <tbody>
            <?php if ( \is_array( $files ) ): ?>
               <?php foreach ( $files as $f ): ?>
                  <tr>
                     <td><input type="text" name="files[<?php print $f[ 'id' ]; ?>][name]" value="<?php print $f[ 'name' ]; ?>"><input type="hidden" name="files[<?php print $f[ 'id' ]; ?>][path]" value="<?php print $f[ 'path' ]; ?>"></td>
                     <td>[img]<?php print $f[ 'path' ]; ?>[/img]</td>
                     <td><button type='button' class="file_delete">删除</button></td>
                  </tr>
               <?php endforeach; ?>
            <?php endif; ?>
         </tbody>
      </table>
      <div>
         <label for="ajax_file_select">上传新附件</label>
         <input type="file" id="file_select" class="form-file" name="attachment[]" multiple="multiple"><button type="button" id="file_upload">上传</button>
         <div class="description">分辨率大于 <em>600x960</em> 的图片将被调整尺寸。 文件最大上传大小为 <em>1 MB</em> 。只允许以下上传文件格式：<em>jpg jpeg gif png</em> 。 </div>
      </div>
   </fieldset>
   <fieldset><button type="submit">发布</button></fieldset>
</form>