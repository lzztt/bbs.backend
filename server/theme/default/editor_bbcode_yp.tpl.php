<div id="editor-div" data-urole='<?php echo $urole_adm; ?>'>
   <form enctype="multipart/form-data" id="editor-form" method="post" accept-charset="UTF-8" action="<?php echo $form_handler; ?>">
      <div id="edit-title" class="form-item">
         <label for="title">标题:<span title="此项必填。" class="form-required">*</span></label>
         <input type="text" class="form-text required" value="<?php echo $title; ?>" size="60" id="title" name="title" maxlength="50" required="required" />
      </div>
      <fieldset class="group-bcard">
         <legend>名片</legend>
         <div id="edit-address-wrapper" class="form-item">
            <label for="edit-address">地址:<span title="此项必填。" class="form-required">*</span></label>
            <input type="text" class="form-text required text" value="<?php echo $address; ?>" size="60" id="edit-address" name="address" required="required" />
         </div>
         <div id="edit-phone-wrapper" class="form-item">
            <label for="edit-phone">电话:<span title="此项必填。" class="form-required">*</span></label>
            <input type="text" class="form-text required text" value="<?php echo $phone; ?>" size="60" id="edit-phone" name="phone" required="required" />
         </div>
         <div id="edit-fax-wrapper" class="form-item">
            <label for="edit-fax">传真:</label>
            <input type="text" class="form-text text" value="<?php echo $fax; ?>" size="60" id="edit-fax" name="fax" />
         </div>
         <div id="edit-email-wrapper" class="form-item">
            <label for="edit-email">电子邮箱:</label>
            <input type="email" class="form-text text" value="<?php echo $email; ?>" size="60" id="edit-email" name="email" />
         </div>
         <div id="edit-website-wrapper" class="form-item">
            <label for="edit-website">网站:</label>
            <input type="url" class="form-text text" value="<?php echo $website; ?>" size="60" id="edit-website" name="website" />
         </div>
      </fieldset>
      <div id="edit-body" class="form-item">
         <label for="BBCodeEditor">正文:</label>
         <textarea rows="20" cols="60" name="body" id="BBCodeEditor" class="text-full form-textarea" required="required"><?php echo $body; ?></textarea>

         <div class="attachments">
            <fieldset class=" collapsible collapsed">
               <legend class="collapse-processed"><a href="#">文件附件</a></legend>
               <div class="fieldset-wrapper">

                  <input type="hidden" name='update_file' value='1' />
                  <table id="ajax-file-list" <?php if (!\is_array($files) || \sizeof($files) < 1): ?> style="display:none;" <?php endif; ?> >
                     <thead class="tableHeader-processed">
                        <tr><th>文字描述</th><th>BBCode</th><th>删除</th></tr>
                     </thead>

                     <tbody>
                        <?php if (\is_array($files)): ?>
                           <?php foreach ($files as $f): ?>
                              <tr id="editfile-<?php echo $f['id']; ?>">
                                 <td><input type="text" maxlength="30" name="files[<?php echo $f['id']; ?>][name]" id="editfile-<?php echo $f['id']; ?>-name" size="30" value="<?php echo $f['name']; ?>" class="form-text"></td>
                                 <td style="padding: 0 10px;">[img]<?php echo $f['path']; ?>[/img]<input type="text" style="display:none;" name="files[<?php echo $f['id']; ?>][path]" value="<?php echo $f['path']; ?>"></td>
                                 <td style="text-align: center;"><a href="/file/delete?id=<?php echo $f['id']; ?>" class="ajax-file-delete" id="editfile-<?php echo $f['id']; ?>-delete">X</a></td>
                              </tr>
                           <?php endforeach; ?>
                        <?php endif; ?>
                     </tbody>
                  </table>

                  <div id="attach-wrapper">
                     <label for="ajax-file-select">上传新附件:</label>
                     <input type="file" id="ajax-file-select" class="form-file" name="yp[]" multiple="multiple" /> <input type="button" id="ajax-file-upload" value="上传" name="attach[]" multiple="multiple" />

                     <div class="description">分辨率大于 <em>600x960</em> 的图片将被调整尺寸。 文件最大上传大小为 <em>1 MB</em> 。只允许以下上传文件格式：<em>jpg jpeg gif png</em> 。 </div>

                  </div>
               </div>
            </fieldset>
         </div>
         <input type="submit" class="form-submit" value="Submit" id="edit-submit"  />
      </div>
   </form>
</div>