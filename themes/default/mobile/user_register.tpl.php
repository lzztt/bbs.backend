<div id="content">
   <div id="content-area">
      <form id="user-register" method="post" accept-charset="UTF-8" action="<?php echo $form_handler; ?>" autocomplete="on">
         <div>
            <fieldset>
               <legend>帐户信息</legend>
               <div id="edit-name-wrapper" class="form-item">
                  <label for="edit-name">用户名:<span title="此项必填。" class="form-required">*</span></label>
                  <input type="text" class="form-text required" value="" size="60" id="edit-name" name="username" maxlength="50" required="required" autofocus="autofocus" />
                  <div class="description">允许空格，不允许"."、“-”、“_”以外的其他符号。</div>
               </div>
               <div id="edit-mail-wrapper" class="form-item">
                  <label for="edit-mail">邮件地址:<span title="此项必填。" class="form-required">*</span></label>
                  <input type="email" class="form-text required" value="" size="60" id="edit-mail" name="email" maxlength="50" required="required" />
                  <div class="description">一个有效的电子邮件地址。帐号激活后的初始密码和所有本站发出的信件都将寄至此地址。电子邮件地址将不会被公开，仅当您想要接收新密码或通知时才会使用。</div>
               </div>
            </fieldset>
            <fieldset>
               <legend>联系方式</legend>
               <div id="edit-user-msn-wrapper" class="form-item">
                  <label for="edit-user-msn">MSN:</label>
                  <input type="email" class="form-text" value="" size="60" id="edit-user-msn" name="msn" maxlength="50" />
               </div>
               <div id="edit-user-qq-wrapper" class="form-item">
                  <label for="edit-user-qq">QQ:</label>
                  <input type="text" class="form-text" value="" size="60" id="edit-user-qq" name="qq" maxlength="50" />
               </div>
               <div id="edit-user-website-wrapper" class="form-item">
                  <label for="edit-user-website">个人网站:</label>
                  <input type="url" class="form-text" value="" size="60" id="edit-user-website" name="website" maxlength="50" />
               </div>
            </fieldset>
            <fieldset><legend>个人信息</legend>
               <div id="edit-profile-sex-wrapper" class="form-item">
                  <label for="edit-profile-sex">姓名 (不会公开显示):</label>
                  名: <input type="text" maxlength="15" name="firstName" id="edit-firstname" size="15" placeholder="First Name" value="" />
                  姓: <input type="text" maxlength="15" name="lastName" id="edit-lastname" size="15" placeholder="Last Name" value="" />
               </div>
               <div id="edit-profile-sex-wrapper" class="form-item">
                  <label for="edit-profile-sex">性别:</label>
                  <select id="edit-profile-sex" class="form-select" name="sex">
                     <option value="null">未选择</option>
                     <option value="1">男</option>
                     <option value="0">女</option>
                  </select>
               </div>
               <div id="edit-profile-age-wrapper" class="form-item">
                  <label for="edit-profile-age">生日 (用于计算年龄和星座，不会公开显示):</label>
                  <select class="form-select" name="byear">
                     <option value="0000">未选择</option>
                     <?php for ($i = 2011; $i >= 1931; $i--): ?>
                        <?php $year = sprintf('%04u', $i); ?>
                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                     <?php endfor; ?>
                  </select> 年
                  <select class="form-select" name="bmonth">
                     <option value="00">未选择</option>
                     <?php for ($i = 1; $i <= 12; $i++): ?>
                        <?php $month = sprintf('%02u', $i); ?>
                        <option value="<?php echo $month; ?>"><?php echo $month; ?></option>
                     <?php endfor; ?>
                  </select> 月
                  <select class="form-select" name="bday">
                     <option value="00">未选择</option>
                     <?php for ($i = 1; $i <= 31; $i++): ?>
                        <?php $day = sprintf('%02u', $i); ?>
                        <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                     <?php endfor; ?>
                  </select> 日
               </div>
               <div id="edit-user-occ-wrapper" class="form-item">
                  <label for="edit-user-occ">职业:</label>
                  <input type="text" class="form-text" value="" size="60" id="edit-user-occ" name="occupation" maxlength="50" />
               </div>
               <div id="edit-profile-interests-wrapper" class="form-item">
                  <label for="edit-profile-interests">兴趣爱好:</label>
                  <input type="text" class="form-text" value="" size="60" id="edit-profile-interests" name="interests" maxlength="50" />
               </div>
               <div id="edit-profile-introduction-wrapper" class="form-item">
                  <label for="edit-profile-introduction">自我介绍:</label>
                  <div class="resizable-textarea">
                     <textarea class="form-textarea resizable textarea-processed" id="edit-profile-introduction" name="favoriteQuotation" rows="5" cols="60"></textarea>
                     <div class="grippie" style="margin-right: -6px;"></div>
                  </div>
               </div>
            </fieldset>
            <fieldset class="captcha"><legend>图形验证CAPTCHA</legend><div class="description">该问题用于确认您是否为真实的访问者。</div>
               <img title="图形验证" alt="图形验证" src="/captcha" />
               <div id="edit-captcha-response-wrapper" class="form-item">
                  <label for="edit-captcha-response">该图片的内容是什么？:<span title="此项必填。" class="form-required">*</span></label>
                  <input type="text" class="form-text required" value="" size="15" id="edit-captcha-response" name="captcha" maxlength="50" required="required" />
                  <div class="description">Enter the characters shown in the image.</div>
               </div>
            </fieldset>
            <input type="submit" class="form-submit" value="创建新帐号" id="edit-submit"/>

         </div>
      </form>
   </div>
</div>
