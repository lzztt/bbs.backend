<header class="content_header">
   <div class="breadcrumb"><?php print $breadcrumb; ?></div>
   <div class="taxonomy-term-description"><?php print $cateDescription; ?></div>
   <?php print $pager; ?>
</header>

<?php if ( $nodes ): ?>
   <div class="ajax_load" data-ajax='<?php print $ajaxURI; ?>'>
      <?php foreach ( $nodes as $n ): ?>
         <div class="bcard">
            <header><a title="<?php print $n[ 'title' ]; ?>" href="/node/<?php print $n[ 'id' ]; ?>"><?php print $n[ 'title' ]; ?></a></header>
            <ul class='clean'>
               <li data-before='地址'><?php print $n[ 'address' ]; ?></li>
               <li data-before='电话'><?php print $n[ 'phone' ]; ?></li>
               <?php if ( isset( $n[ 'fax' ] ) ): ?>
                  <li data-before='传真'><?php print $n[ 'fax' ]; ?></li>
               <?php endif; ?>
               <?php if ( isset( $n[ 'email' ] ) ): ?>
                  <li data-before='电子邮箱'><?php print $n[ 'email' ]; ?></li>
               <?php endif; ?>
               <?php if ( isset( $n[ 'website' ] ) ): ?>
                  <li data-before='网站'><?php print $n[ 'website' ]; ?></li>
               <?php endif; ?>
            </ul>
            <footer><span class="ajax_viewCount<?php print $n[ 'id' ]; ?>"></span>次浏览，<?php print $n[ 'rating_count' ]; ?>人评分，<?php print $n[ 'comment_count' ]; ?>条评论</footer>
         </div>
      <?php endforeach; ?>
   </div>
<?php endif; ?>
<?php print $pager; ?>