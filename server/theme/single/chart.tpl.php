<?php foreach ( $stat as $dist ): ?>
   <div id="<?php print $dist[ 'div_id' ]; ?>"></div>
   <script type='text/javascript'>
   <?php print "drawChart('${dist[ 'title' ]}', $.parseJSON('${dist[ 'data' ]}'), '${dist[ 'div_id' ]}')"; ?>
   </script>
<?php endforeach; ?>