<form accept-charset="UTF-8" autocomplete="off" method="post" action="/adm/ad/add" id="ad-add">
   <div class='form_element'>
      <label data-help="广告名称">名称</label><input size="22" name="name" type="text" required="required" />
   </div>
   <div class='form_element'>
      <label data-help="联系Email">Email</label><input size="22" name="email" type="text" required="required" />
   </div>
   <div class='form_element'>
      <label data-help="广告类别">类别</label><select class="form_element" name="type_id">
         <option value="1">电子黄页</option>
         <option value="2">页顶</option>
      </select>
   </div>
   <div class='form_element'>
      <button type="submit">添加广告</button>
   </div>
</form>