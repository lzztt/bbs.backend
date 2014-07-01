<form method="post" action="/wedding/add" name="contactform" id="contactform" autocomplete="on">
      <div>
         <label for="name" accesskey="U">姓名</label>
         <input name="name" type="text" id="name" placeholder="中文姓名" required="required" />
      </div>

      <div>
         <label for="guests">人数</label>
         <input name="guests" type="text" id="guests" placeholder="请填写一共多少人来参加答谢宴" />
      </div>

      <div>
         <label for="email" accesskey="E">Email</label>
         <input name="email" type="email" id="email" placeholder="输入个人电子邮箱" pattern="^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,})$" />
      </div>
      <div>
         <label for="phone" accesskey="P">电话</label>
         <input name="phone" type="tel" id="phone" size="30" placeholder="输入电话号码" />
      </div>
      <div>
         <input type="submit" class="submit" id="submit" value="添加新记录" />
      </div>
</form>
