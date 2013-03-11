<div id="content">
   <div id="content-header">

      <h1 class="title"><?php echo $user->username; ?></h1>
      <div class="tabs">
         <ul class="tabs primary clear-block">
            <li><a href="/user/<?php echo $user->uid; ?>"><span class="tab">查看</span></a></li>
            <?php if ($uid == 1 || $uid == $user->uid): ?>
               <li class="active"><a class="active" href="/user/<?php echo $user->uid; ?>/edit"><span class="tab">编辑</span></a></li>
               <li><a href="/pm"><span class="tab">站内短信</span></a></li>
            <?php endif; ?>
            <li><a href="/user/<?php echo $user->uid; ?>/track"><span class="tab">跟踪</span></a></li>
         </ul>
      </div>
   </div>

   <div id="content-area">
      <form enctype="multipart/form-data" id="user-profile-form" method="post" accept-charset="UTF-8" action="<?php echo $form_handler; ?>">
         <div>
            <fieldset><legend>头像</legend>
               <div class="picture">
                  <img title="<?php echo $user->username; ?> 的头像" alt="<?php echo $user->username; ?> 的头像" src="<?php echo ($user->avatar ? $user->avatar : '/data/avatars/avatar0' . mt_rand(1, 5) . '.jpg'); ?>">
               </div>
               <div id="edit-picture-upload-wrapper" class="form-item">
                  <label for="edit-picture-upload">上传图片:</label>
                  <input type="file" size="48" id="edit-picture-upload" class="form-file" name="avatar" />

                  <div class="description">您的虚拟头像。最大尺寸是 <em>90x90</em> 像素，最大大小为 <em>30</em> KB。 </div>
               </div>
            </fieldset>
            <fieldset>
               <legend>修改密码</legend>
               <div id="edit-pass-wrapper" class="form-item">
                  <div id="edit-pass-pass1-wrapper" class="form-item password-parent">
                     <label for="edit-pass-pass1">新密码 (如果不想修改密码，此项请留空):</label>
                     <input type="password" class="form-text password-field password-processed" size="25" maxlength="128" id="edit-pass-pass1" name="password[1]" /><span class="password-strength"><span class="password-title">密码强度：</span> <span class="password-result"></span></span>
                  </div>
                  <div id="edit-pass-pass2-wrapper" class="form-item confirm-parent">
                     <label for="edit-pass-pass2">确认新密码:</label>
                     <input type="password" class="form-text password-confirm" size="25" maxlength="128" id="edit-pass-pass2" name="password[2]" /><span class="password-confirm">密码匹配：  <span></span></span>
                  </div><div class="password-description" style="display: none;"></div>

                  <div class="description">若要更改当前用户密码，请重复输入新密码。</div>
               </div>
            </fieldset>
            <fieldset>
               <legend>联系方式</legend>
               <div id="edit-user-msn-wrapper" class="form-item">
                  <label for="edit-user-msn">MSN:</label>
                  <input type="email" class="form-text" value="<?php echo $user->msn; ?>" size="60" id="edit-user-msn" name="msn" maxlength="50" />
               </div>
               <div id="edit-user-qq-wrapper" class="form-item">
                  <label for="edit-user-qq">QQ:</label>
                  <input type="text" class="form-text" value="<?php echo $user->qq; ?>" size="60" id="edit-user-qq" name="qq" maxlength="50" />
               </div>
               <div id="edit-user-website-wrapper" class="form-item">
                  <label for="edit-user-website">个人网站:</label>
                  <input type="url" class="form-text" value="<?php echo $user->website; ?>" size="60" id="edit-user-website" name="website" maxlength="50" />
               </div>
            </fieldset>
            <a name="personal"></a>
            <fieldset><legend>个人信息</legend>
               <div id="edit-profile-sex-wrapper" class="form-item">
                  <label for="edit-profile-sex">姓名 (不会公开显示):</label>
                  名: <input type="text" maxlength="15" name="firstName" id="edit-firstname" size="15" placeholder="First Name" value="<?php echo $user->firstName; ?>" />
                  姓: <input type="text" maxlength="15" name="lastName" id="edit-lastname" size="15" placeholder="Last Name" value="<?php echo $user->lastName; ?>" />
               </div>
               <div id="edit-profile-sex-wrapper" class="form-item">
                  <label for="edit-profile-sex">性别:</label>
                  <select id="edit-profile-sex" class="form-select" name="sex">
                     <option value="null">未选择</option>
                     <option <?php echo $user->sex === 1 ? 'selected="selected"' : ''; ?> value="1">男</option>
                     <option <?php echo $user->sex === 0 ? 'selected="selected"' : ''; ?> value="0">女</option>
                  </select>
               </div>
               <?php
               if ($user->birthday):
                  $birthday = sprintf('%08u', $user->birthday);
                  $byear = substr($birthday, 0, 4);
                  $bmonth = substr($birthday, 4, 2);
                  $bday = substr($birthday, 6, 2);
               endif;
               ?>
               <div id="edit-profile-age-wrapper" class="form-item">
                  <label for="edit-profile-age">生日 (用于计算年龄和星座，不会公开显示):</label>
                  <select class="form-select" name="byear">
                     <option value="0000">未选择</option>
                     <?php for ($i = 2011; $i >= 1931; $i--): ?>
                        <?php $year = sprintf('%04u', $i); ?>
                        <option <?php echo $byear == $year ? 'selected="selected"' : ''; ?> value="<?php echo $year; ?>"><?php echo $year; ?></option>
                     <?php endfor; ?>
                  </select> 年
                  <select class="form-select" name="bmonth">
                     <option value="00">未选择</option>
                     <?php for ($i = 1; $i <= 12; $i++): ?>
                        <?php $month = sprintf('%02u', $i); ?>
                        <option <?php echo $bmonth == $month ? 'selected="selected"' : ''; ?> value="<?php echo $month; ?>"><?php echo $month; ?></option>
                     <?php endfor; ?>
                  </select> 月
                  <select class="form-select" name="bday">
                     <option value="00">未选择</option>
                     <?php for ($i = 1; $i <= 31; $i++): ?>
                        <?php $day = sprintf('%02u', $i); ?>
                        <option <?php echo $bday == $day ? 'selected="selected"' : ''; ?> value="<?php echo $day; ?>"><?php echo $day; ?></option>
                     <?php endfor; ?>
                  </select> 日
               </div>
               <div id="edit-user-occ-wrapper" class="form-item">
                  <label for="edit-user-occ">职业:</label>
                  <input type="text" class="form-text" value="<?php echo $user->occupation; ?>" size="60" id="edit-user-occ" name="occupation" maxlength="50" />
               </div>
               <div id="edit-profile-interests-wrapper" class="form-item">
                  <label for="edit-profile-interests">兴趣爱好:</label>
                  <input type="text" class="form-text" value="<?php echo $user->interests; ?>" size="60" id="edit-profile-interests" name="interests" maxlength="50" />
               </div>
               <div id="edit-profile-introduction-wrapper" class="form-item">
                  <label for="edit-profile-introduction">自我介绍:</label>
                  <div class="resizable-textarea">
                     <textarea class="form-textarea resizable textarea-processed" id="edit-profile-introduction" name="favoriteQuotation" rows="5" cols="60"><?php echo $user->favoriteQuotation; ?></textarea>
                     <div class="grippie" style="margin-right: -6px;"></div>
                  </div>
               </div>
            </fieldset>
            <fieldset><legend>语言设置</legend>
               <div class="form-item">
                  <label>语言:</label>
                  <div class="form-radios"><div id="edit-language-zh-hans-wrapper" class="form-item">
                        <label for="edit-language-zh-hans" class="option">
                           <input type="radio" class="form-radio" checked="checked" value="zh-hans" name="language" id="edit-language-zh-hans" /> Chinese, Simplified(简体中文) (简体中文)</label>
                     </div>
                     <div id="edit-language-en-wrapper" class="form-item">
                        <label for="edit-language-en" class="option">
                           <input type="radio" class="form-radio" value="en" name="language" id="edit-language-en" /> English(英语) (English)</label>
                     </div>
                  </div>
                  <div class="description">本账户的电子邮件的默认语言</div>
               </div>
            </fieldset>
            <fieldset><legend>签名设置</legend><div id="edit-signature-wrapper" class="form-item">
                  <label for="edit-signature">签名档:</label>
                  <div class="resizable-textarea"><span>
                        <textarea class="form-textarea resizable textarea-processed" id="edit-signature" name="signature" rows="5" cols="60"><?php echo $user->signature; ?></textarea>
                        <div class="grippie" style="margin-right: -6px;"></div></span></div>
                  <div class="description">您的签名将会公开显示在评论的末尾。</div>
               </div>
            </fieldset>
            <input type="submit" class="form-submit" value="保存" id="edit-submit"/>
         </div>
      </form>
   </div>
</div>