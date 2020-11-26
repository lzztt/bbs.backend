<?php
function (
  string $address,
  array $ads,
  string $body,
  string $email,
  array $files,
  string $phone,
  string $title,
  string $website
) {
?>

  <form enctype="multipart/form-data" id="bbcode_editor" class='v_user_superadm v_user_8831 v_user_3' method="post" accept-charset="UTF-8" action="">
    <?php if (!empty($ads)) : ?>
      <fieldset>
        <label class='label' for="title">黄页广告:</label>
        <select name='aid'>
          <?php foreach ($ads as $a) : ?>
            <option value="<?= $a['id'] ?>"><?= $a['name'] ?></option>
          <?php endforeach ?>
        </select>
      </fieldset>
    <?php endif ?>
    <fieldset>
      <label class='label' for="title">名称:</label>
      <input type="text" name="title" value="<?= empty($title) ? '' : $title ?>" required placeholder="最少5个字母或3个汉字">
    </fieldset>
    <fieldset>
      <label class='label' for="address">地址:</label>
      <input type="text" value="<?= empty($address) ? '' : $address ?>" name="address" required />
    </fieldset>
    <fieldset>
      <label class='label' for="phone">电话:</label>
      <input type="tel" value="<?= empty($phone) ? '' : $phone ?>" name="phone" required />
    </fieldset>
    <fieldset>
      <label class='label' for="email">电子邮箱:</label>
      <input type="email" value="<?= empty($email) ? '' : $email ?>" name="email" />
    </fieldset>
    <fieldset>
      <label class='label' for="website">网站:</label>
      <input type="url" value="<?= empty($website) ? '' : $website ?>" name="website" />
    </fieldset>
    <fieldset>
      <label class='label' for="body">内容介绍</label>
      <textarea name="body" required placeholder="最少5个字母或3个汉字"><?= empty($body) ? '' : $body ?></textarea>
    </fieldset>
    <fieldset>
      <label class='label'>上传图片</label>
      <input type="hidden" name='update_file' value='1'>
      <div id="file_list">
        <?php if (!empty($files)) : ?>
          <script>
            function fig_self_delete(ev) {
              var fig = ev.target.parentElement.parentElement;
              fig.parentElement.removeChild(fig);
            }
          </script>
          <?php foreach ($files as $f) : ?>
            <figure>
              <img src="<?= $f['path'] ?>" width="200">
              <figcaption width="200">
                <input type="text" value="<?= $f['id'] ?>" name="file_id[]" hidden>
                <label>名称</label><input type="text" value="<?= $f['name'] ?>" size=15 name="file_name[]"><br>
                <label>代码</label><input disabled type="text" value="[img]<?= $f['path'] ?>[/img]" size=15 name="file_code[]"><br>
                <button type="button" onclick="fig_self_delete(event)">删除</button>
              </figcaption>
            </figure>
          <?php endforeach ?>
        <?php endif ?>
      </div>
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
        <input type="file" id="file_select">
        <div class="description">宽度大于 <em>600</em> 像素的图片将被调整尺寸。</div>
      </div>
    </fieldset>
    <fieldset><button type="submit">发布</button></fieldset>
  </form>

<?php
};
