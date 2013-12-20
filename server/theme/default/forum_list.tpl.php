<div id="content">
  <?php if ($forum['id'] != 1): ?><div class="breadcrumb"><a href="/forum">Forum</a></div><?php endif; ?>

  <div id="forum">
    <div class="forum-top-links"></div>
    <table id="forum-0" class="forum-table forums-overview">
      <tbody class="js_even_odd_parent">
        <?php foreach ($forum['children'] as $group): ?>
          <tr id="forum-list-1" class="first-row container container-1" >
            <td colspan="2" class="container">
              <div class="forum-details">
                <div class="name">
                  <a href="/forum/<?php echo $group['id']; ?>"><?php echo $group['name']; ?></a>
                </div>
                <div class="description"><?php echo $group['description']; ?></div>
              </div>
            </td>

            <td class="topics">
              主题
            </td>
            <td class="topics">
              回复
            </td>

            <td class="last-reply">最新文章</td>

          </tr>

          <?php foreach ($group['children'] as $board): ?>
            <tr id="forum-list-4" class="middle-row in-container-0">

              <td class="forum-icon"> <img src="/themes/default/images/forum/forum-folder.png" alt="文件夹" title="文件夹" width="33" height="29" /> </td>

              <td>
                <div class="forum-details">

                  <div class="name"><a href="/forum/<?php echo $board['id']; ?>"><?php echo $board['name']; ?></a></div>
                  <div class="description"><?php echo $board['description']; ?></div>
                </div>
              </td>

              <td class="topics">
                <?php echo $board['nodeInfo']['nodeCount']; ?>
              </td>
              <td class="topics">
                <?php echo $board['nodeInfo']['commentCount']; ?>
              </td>

              <td class="last-reply">
                <a href="/node/<?php echo $board['nodeInfo']['nid']; ?>"><?php echo $board['nodeInfo']['title']; ?></a><br />作者 <?php echo $board['nodeInfo']['username']; ?><br /><?php echo ($board['nodeInfo']['createTime']); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endforeach; ?>

      </tbody>
    </table>

    <div class="forum-folder-legend forum-smalltext clear-block">
      <dl>
        <dt><img src="/themes/default/images/forum/forum-folder-new-posts.png" alt="有新贴" title="有新贴" width="33" height="29" /></dt>
        <dd>有新贴</dd>

        <dt><img src="/themes/default/images/forum/forum-folder.png" alt="有新回复" title="有新回复" width="33" height="29" /></dt>
        <dd>有新回复</dd>
        <dt><img src="/themes/default/images/forum/forum-folder-locked.png" alt="论坛被锁定" title="论坛被锁定" width="33" height="29" /></dt>
        <dd>论坛被锁定</dd>
      </dl>
    </div>

    <div style="border:1px solid #DDDDDD; color:#666666; margin-top:15px; clear: both;">
      <div style="padding:0.4em 0.7em;">

        <a href="/node/210">Links (点击申请 友情链接)</a>
      </div>
      <div style="padding:0.4em 0.7em; background:#EEEEEE none repeat scroll 0 0;">
        <a style="padding: 4px;" target="_blank" href="http://www.ricebbs.net">Rice BBS</a>
        <a style="padding: 4px;" target="_blank" href="http://www.hellogwu.com">GWUCSSA</a>
        <a style="padding: 4px;" target="_blank" href="http://www.utcssa.net">UT Austin CSSA</a>
        <a style="padding: 4px;" target="_blank" href="http://www.enteme.net">海外之缘</a>

        <a style="padding: 4px;" target="_blank" href="http://www.uslifes.com">海外生活</a>
        <a style="padding: 4px;" target="_blank" href="http://www.xibian.net">西边摄影</a>
        <a style="padding: 4px;" target="_blank" href="http://www.hi8d.com">HI8D国际短信</a>
      </div>
    </div>
  </div>
</div>