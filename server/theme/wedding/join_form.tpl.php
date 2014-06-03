<form method="post" action="/wedding/join" name="contactform" id="contactform" autocomplete="on">

   <fieldset>

      <legend>Contact Details</legend>

      <div>
         <label for="name" accesskey="U">姓名</label>
         <input name="name" type="text" id="name" placeholder="中文姓名" required="required" />
      </div>

      <div>
         <label for="count">人数</label>
         <input name="count" type="text" id="count" placeholder="请填写一共多少人来参加婚宴" />
      </div>

      <div>
         <label for="email" accesskey="E">Email</label>
         <input name="email" type="email" id="email" placeholder="输入你的个人电子邮箱" pattern="^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,})$" required="required" />
      </div>
      <div>
         <label for="phone" accesskey="P">电话<small>(optional)</small></label>
         <input name="phone" type="tel" id="phone" size="30" placeholder="输入你的电话号码" />
      </div>
      <div>
         <input type="submit" class="submit" id="submit" value="Submit" />
      </div>
   </fieldset>

</form>
