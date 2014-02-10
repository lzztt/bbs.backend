<div id="content">

   <div id="content-header">
      <div class="breadcrumb"><?php echo $breadcrumb; ?></div>
   </div> <!-- /#content-header -->

   <div id="content-area">
      <div class="taxonomy-term-description"><?php echo $cateDescription; ?></div>

      <?php if (isset($pager)): ?>
         <div class="item-list"><ul class="pager"><?php echo $pager; ?></ul></div>
      <?php endif; ?>

      <?php if (isset($nodes)): ?>
         <div id="ajax_node_list">
            <?php foreach ($nodes as $n): ?>
               <div class="node node-mine node-teaser node-type-yp">
                  <div class="node-inner">

                     <h2 class="title">
                        <a title="<?php echo $n['title']; ?>" href="/node/<?php echo $n['id']; ?>"><?php echo $n['title']; ?></a>
                     </h2>
                     <span id="ajax_viewCount_<?php echo $n['id']; ?>"></span>次浏览，<?php echo $n['rating_count']; ?>人评分，<?php echo $n['comment_count']; ?>条评论
                     <div class="bcard">
                        <table>
                           <tbody>
                              <tr><td class="text_right">地址:</td><td><?php echo $n['address']; ?></td></tr>
                              <tr><td class="text_right">电话:</td><td><?php echo $n['phone']; ?></td></tr>
                              <?php if (isset($n['fax'])): ?>
                                 <tr><td class="text_right">传真:</td><td><?php echo $n['fax']; ?></td></tr>
                              <?php endif; ?>
                              <?php if (isset($n['email'])): ?>
                                 <tr><td class="text_right">电子邮箱:</td><td><?php echo $n['email']; ?></td></tr>
                              <?php endif; ?>
                              <?php if (isset($n['website'])): ?>
                                 <tr><td class="text_right">网站:</td><td><?php echo $n['website']; ?></td></tr>
                              <?php endif; ?>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div> <!-- /node-inner, /node -->
            <?php endforeach; ?>
         </div>
         <script type="text/javascript">
            $(document).ready(function() {
               $.getJSON('<?php echo $ajaxURI; ?>', function(data){
                  var stat = $('#ajax_node_list');
                  for (var prop in data)
                  {
                     $('#ajax_' + prop, stat).html(data[prop]);
                  }
               });
            });
         </script>
      <?php endif; ?>

      <?php if (isset($pager)): ?>
         <div class="item-list"><ul class="pager"><?php echo $pager; ?></ul></div>
      <?php endif; ?>
   </div>

</div>