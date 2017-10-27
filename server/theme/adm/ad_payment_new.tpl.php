<form accept-charset="UTF-8" autocomplete="off" method="post" action="/adm/ad/payment" id="adpayment-add">
  <div class="form_element">
    <label>广告名称</label><select class="form_element" name="ad_id">
      <?php foreach ($ads as $a): ?>
        <option value="<?= $a['id'] ?>"><?= $a['name'] ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="form_element">
    <label data-help="付款金额，单位为美元">金额 ($)</label><input size="22" name="amount" type="text" required="required">
  </div>
  <div class="form_element">
    <label data-help="付款时间">付款时间</label><input size="22" name="time" type="text" value="<?= $date ?>" required="required">
  </div>
  <div class="form_element">
    <label data-help="广告有效时间，单位为月">广告时间 (月)</label><input size="22" name="ad_time" type="text" value="3" required="required">
  </div>
  <div class="form_element">
    <label data-help="付款备注">备注</label><textarea rows="5" cols="50" name="comment" required="required"></textarea>
  </div>
  <div class="form_element"><button type="submit">添加付款</button></div>
</form>