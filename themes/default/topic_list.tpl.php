<div id="content">

   <div id="content-header">
      <div class="breadcrumb"><?php echo $breadcrumb; ?></div>
      <h1 class="title"><?php echo $boardName; ?></h1>
   </div> <!-- /#content-header -->

   <div id="content-area">
      <div id="forum">

         <div class="forum-description"><?php echo $boardDescription; ?></div>

         <div class="forum-top-links">
            <ul class="links forum-links">
               <li data-urole='<?php echo $urole_user; ?>'><a rel="nofollow" class="bb-create-node button" href="/forum/<?php echo $tid; ?>/node">发表新话题</a></li>
               <li data-urole='<?php echo $urole_guest; ?>'>您需要先<a rel="nofollow" href="/user">登录</a>或<a href="/user/register">注册</a>才能发表新话题</li>
            </ul>
         </div>

         <?php if (isset($pager)): ?>
            <div class="item-list"><ul class="pager"><?php echo $pager; ?></ul></div>
         <?php endif; ?>
         <table id="forum-topic-4" class="forum-topics">
            <thead>
               <tr>
                  <th class="topic-icon"></th>
                  <th class="topic-topic">主题</th>
                  <th class="topic-replies">回复</th>
                  <th class="topic-replies">浏览</th>
                  <th class="topic-created">作者</th>
                  <th class="topic-lreply active">最后回复</th>
               </tr>
            </thead>

            <tbody class='js_even_odd_parent' id="ajax_node_list">
               <?php foreach ($nodes as $node): ?>
                  <tr class="<?php echo ($node['weight'] >= 1) ? ' sticky-topic' : ''; ?>">
                     <td class="icon"><div class="forum-icon">

                           <img src="/themes/default/images/forum/topic-<?php echo ($node['weight'] >= 1) ? 'sticky' : 'default' ?>.png" alt="" title="" width="22" height="22" />
                        </div></td>

                     <td class="title">
                        <?php echo ($node['weight'] >= 1) ? '置顶: ' : '' ?><a href="/node/<?php echo $node['nid']; ?>"><?php echo $node['title']; ?></a>            </td>

                     <td class="replies">
                        <div class="num num-replies"><?php echo $node['commentCount']; ?></div>
                     </td>

                     <td class="replies">
                        <div class="num num-view" id="ajax_viewCount_<?php echo $node['nid']; ?>"></div>
                     </td>


                     <td class="created">
                        作者 <?php echo $node['createrName']; ?><br /><?php echo ($node['createTime']); ?></td>

                     <td class="last-reply">
                        <?php if ($node['commentCount'] > 0): ?>
                           作者 <?php echo $node['lastCommenterName']; ?><br /><?php echo ($node['lastCommentTime']); ?>
                        <?php endif; ?>
                     </td>
                  </tr>
               <?php endforeach; ?>

            </tbody>
         </table>
         <script type="text/javascript">
            $(document).ready(function() {
               $.getJSON('<?php echo $ajaxURI; ?>', function(data) {
                  var stat = $('#ajax_node_list');
                  for (var prop in data)
                  {
                     $('#ajax_' + prop, stat).html(data[prop]);
                  }
               });
            });
         </script>

         <?php echo $editor; ?>

         <div class="forum-topic-legend forum-smalltext clear-block">
            <div class="legend-group">
               <dl>
                  <dt><img src="/themes/default/images/forum/topic-default.png" alt="没有新贴" title="没有新贴" width="22" height="22" /></dt>
                  <dd>没有新贴</dd>
               </dl>

               <dl>
                  <dt><img src="/themes/default/images/forum/topic-new.png" alt="新贴" title="新贴" width="22" height="22" /></dt>
                  <dd>新贴</dd>
               </dl>
            </div>

            <div class="legend-group">
               <dl>
                  <dt><img src="/themes/default/images/forum/topic-hot.png" alt="过去热贴" title="过去热贴" width="22" height="22" /></dt>

                  <dd>过去热贴</dd>
               </dl>
               <dl>
                  <dt><img src="/themes/default/images/forum/topic-hot-new.png" alt="最新热贴" title="最新热贴" width="22" height="22" /></dt>
                  <dd>最新热贴</dd>
               </dl>
            </div>

            <div class="legend-group">
               <dl>
                  <dt><img src="/themes/default/images/forum/topic-sticky.png" alt="置顶热点" title="置顶热点" width="22" height="22" /></dt>
                  <dd>置顶热点</dd>
               </dl>
               <dl>
                  <dt><img src="/themes/default/images/forum/topic-closed.png" alt="锁定的讨论" title="锁定的讨论" width="22" height="22" /></dt>
                  <dd>锁定的讨论</dd>
               </dl>
            </div>
         </div>

         <?php if (isset($pager)): ?>
            <div class="item-list"><ul class="pager"><?php echo $pager; ?></ul></div>
            <?php endif; ?>
      </div>
   </div>

</div>