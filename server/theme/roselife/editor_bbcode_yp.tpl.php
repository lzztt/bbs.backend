<form enctype="multipart/form-data" id="bbcode_editor" class='v_user_superadm v_user_8831 v_user_3' method="post" accept-charset="UTF-8" action="<?= $form_handler ?>">
  <?php if (is_array($ads)): ?>
  <fieldset>
    <label class='label' for="title">黄页广告:</label>
    <select name='aid'>
      <?php foreach ($ads as $a): ?>
      <option value="<?= $a['id'] ?>"><?= $a['name'] ?></option>
      <?php endforeach ?>
    </select>
  </fieldset>
  <?php endif ?>
  <fieldset>
    <label class='label' for="title">名称:</label>
    <input type="text" name="title" value="<?= $title ?>" required placeholder="最少5个字母或3个汉字">
  </fieldset>
  <fieldset>
    <label class='label' for="address">地址:</label>
    <input type="text" value="<?= $address ?>" name="address" required />
  </fieldset>
  <fieldset>
    <label class='label' for="phone">电话:</label>
    <input type="tel" value="<?= $phone ?>" name="phone" required />
  </fieldset>
  <fieldset>
    <label class='label' for="email">电子邮箱:</label>
    <input type="email" value="<?= $email ?>" name="email" />
  </fieldset>
  <fieldset>
    <label class='label' for="website">网站:</label>
    <input type="url" value="<?= $website ?>" name="website" />
  </fieldset>
  <fieldset>
    <label class='label' for="body">内容介绍</label>
    <textarea name="body" required placeholder="最少5个字母或3个汉字"><?= $body ?></textarea>
  </fieldset>
  <fieldset>
    <label class='label'>文件附件</label>
    <input type="hidden" name='update_file' value='1'>
    <table id="file_list" <?php if (!is_array($files) || sizeof($files) < 1): ?> style="display:none;" <?php endif ?>>
      <thead><tr><th>图片名</th><th>BBCode</th><th>删除</th></tr></thead>
      <tbody>
        <?php if (is_array($files)): ?>
          <?php foreach ($files as $f): ?>
            <tr>
              <td><input type="text" name="files[<?= $f['id'] ?>][name]" value="<?= $f['name'] ?>"><input type="hidden" name="files[<?= $f['id'] ?>][path]" value="<?= $f['path'] ?>"></td>
              <td>[img]<?= $f['path'] ?>[/img]</td>
              <td><button type='button' class="file_delete">删除</button></td>
            </tr>
          <?php endforeach ?>
        <?php endif ?>
      </tbody>
    </table>
    <div>
      <label for="ajax_file_select">上传新附件</label>
      <input type="file" id="file_select" class="form-file" name="attachment[]" multiple="multiple"><button type="button" id="file_upload">上传</button> <button type="button" id="file_clear">取消选中图片</button>
      <div class="description">分辨率大于 <em>600x960</em> 的图片将被调整尺寸。 文件最大上传大小为 <em>1 MB</em> 。只允许以下上传文件格式：<em>jpg jpeg gif png</em> 。 </div>
    </div>
  </fieldset>
  <fieldset><button type="submit">发布</button></fieldset>
</form>
