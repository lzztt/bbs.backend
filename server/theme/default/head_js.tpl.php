<script>
   if ('querySelector' in document && 'localStorage' in window && 'addEventListener' in window) {
      document.write('<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"><\/script>');
   } else {
      document.write('<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"><\/script>');
   }
</script>
<script>
   if (!window.jQuery)
   {
      if ('querySelector' in document && 'localStorage' in window && 'addEventListener' in window) {
         document.write('<script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-2.1.0.min.js"><\/script>');
      }
      else {
         document.write('<script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.0.min.js"><\/script>');
      }
   }
</script>
<script>
   if (!window.jQuery) {
      if ('querySelector' in document && 'localStorage' in window && 'addEventListener' in window) {
         document.write('<script src="http://code.jquery.com/jquery-2.1.0.min.js"><\/script>');
      }
      else {
         document.write('<script src="http://code.jquery.com/jquery-1.11.0.min.js"><\/script>');
      }
   }
</script>

<script>(typeof JSON === 'object') || document.write('<script src="/themes/<?php echo $tpl_theme; ?>/js/json2.js"><\/script>')</script>

<script src="/themes/<?php echo $tpl_theme; ?>/js/min_1367737911.js"></script>
<?php
/* DEV
  <script src="/themes/<?php echo $tpl_theme; ?>/js/jquery.cookie.js"></script>
  <script src="/themes/<?php echo $tpl_theme; ?>/js/jquery.upload-1.0.2.js"></script>
  <script src="/themes/<?php echo $tpl_theme; ?>/js/jquery.markitup.js"></script>
  <script src="/themes/<?php echo $tpl_theme; ?>/js/jquery.markitup.bbcode.set.js"></script>
  <script src="/themes/<?php echo $tpl_theme; ?>/js/hoverIntent.js"></script>
  <script src="/themes/<?php echo $tpl_theme; ?>/js/superfish.js"></script>
  <script src="/themes/<?php echo $tpl_theme; ?>/js/coin-slider.js"></script>
  <script src="/themes/<?php echo $tpl_theme; ?>/js/jquery.MetaData.js"></script>
  <script src="/themes/<?php echo $tpl_theme; ?>/js/jquery.rating.js"></script>
  <script src="/themes/<?php echo $tpl_theme; ?>/js/main.js"></script>
 */
?>