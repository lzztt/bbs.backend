<div class="breadcrumb"><?php echo $breadcrumb; ?></div>
<h1 class="title"><?php echo $cateName; ?></h1>
<div class="taxonomy-term-description"><?php echo $cateDescription; ?></div>

<?php if (isset($pager)): ?>
   <div class="item-list"><ul class="pager"><?php echo $pager; ?></ul></div>
<?php endif; ?>

<?php if (isset($nodes)): ?>
   <ul>
      <?php foreach ($nodes as $n): ?>
         <li>
            <ul>
               <li><a title="<?php echo $n['title']; ?>" href="/node/<?php echo $n['nid']; ?>"><?php echo $n['title']; ?></a></li>
               <li>地址: <?php echo $n['address']; ?></li>
               <li>电话: <?php echo $n['phone']; ?></li>
               <?php if (isset($n['fax'])): ?>
                  <li>传真: <?php echo $n['fax']; ?></li>
               <?php endif; ?>
               <?php if (isset($n['email'])): ?>
                  <li>电子邮箱: <?php echo $n['email']; ?></li>
               <?php endif; ?>
               <?php if (isset($n['website'])): ?>
                  <li>网站: <?php echo $n['website']; ?></li>
               <?php endif; ?>
            </ul>
         </li>
      <?php endforeach; ?>
   </ul>
<?php endif; ?>

<?php if (isset($pager)): ?>
   <div class="item-list"><ul class="pager"><?php echo $pager; ?></ul></div>
<?php endif; ?>