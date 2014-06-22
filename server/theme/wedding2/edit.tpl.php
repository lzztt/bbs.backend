<form method="post" action="/wedding/edit" name="edit" autocomplete="on">
   <input type="hidden" name='id' value='<?php print $id; ?>' />
   <div>
      <label for="name" accesskey="U">姓名</label>
      <input name="name" type="text" id="name" value="<?php print $name; ?>" placeholder="中文姓名" required="required" />
   </div>

   <div>
      <label for="guests">人数</label>
      <input name="guests" type="text" id="guests" value="<?php print $guests; ?>" placeholder="请填写一共多少人来参加答谢宴" />
   </div>

   <div>
      <label for="email" accesskey="E">Email</label>
      <input name="email" type="email" id="email" value="<?php print $email; ?>" placeholder="输入你的个人电子邮箱" pattern="^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,})$" required="required" />
   </div>

   <div>
      <label for="phone" accesskey="P">电话</label>
      <input name="phone" type="tel" id="phone" value="<?php print $phone; ?>" placeholder="输入你的电话号码" />
   </div>

   <div>
      <label for="checkin" accesskey="P">签到</label>
      <input name="checkin" type="number" id="checkin" value="<?php print $checkin; ?>" placeholder="签到时间戳" />
   </div>

   <div>
      <input type="submit" class="submit" id="submit" value="保存更新" />
   </div>
</form>