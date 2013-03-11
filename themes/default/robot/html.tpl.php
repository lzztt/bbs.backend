<!DOCTYPE html>
<html lang='zh' dir='ltr'>
   <head>
      <meta charset='UTF-8' />
      <meta name='description' content='<?php echo $head_description; ?>' />
      <title><?php echo $head_title; ?></title>
   </head>
   <body>
      <div style='position:relative; height:150px'>
         <?php include $tpl_path . '/head_ad.tpl.php'; ?>
      </div>
      <div id="article" class="content article">
         <?php echo $content; ?>
      </div>
      <?php include $tpl_path . '/page_header_footer.tpl.php'; ?>
      <script type="text/javascript">
         var _gaq = _gaq || [];
         _gaq.push(['_setAccount', 'UA-36671672-1']);
         _gaq.push(['_trackPageview']);

         (function() {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
         })();
      </script>
   </body>
</html>