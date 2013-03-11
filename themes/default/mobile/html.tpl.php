<!DOCTYPE html>
<html lang='zh' dir='ltr'>
   <head>
      <meta charset='UTF-8' />
      <meta name='description' content='<?php echo $head_description; ?>' />
      <?php echo $head_css; ?>
      <?php echo $head_js; ?>
      <title><?php echo $head_title; ?></title>
      <link rel='apple-touch-icon' href='/apple-touch-icon.png' />
      <link rel='apple-touch-icon' sizes='72x72' href='/apple-touch-icon-72x72.png' />
      <link rel='apple-touch-icon' sizes='114x114' href='/apple-touch-icon-114x114.png' />
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <style type='text/css' media="all">
         ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
         }
         li {
            padding: 1px 0;
         }
         ul.pager li {
            display: inline-block;
            padding: 3px 5px;
            background-color: pink;
         }
         ul.pager li.pageActive {
            background-color: peru;
         }
         a {
            color: #105289;
            text-decoration: none;
         }
         table {
            border: 1px solid blueviolet;
            margin: 5px 1px;
            padding: 2px;
         }
         img {
            max-width: 600px;
         }
         span.li_right {
            color: #333333;
            float: right;
            padding-left: 5px;
         }
         .post-info {
            background-color: #F0E68C;
            color: #006400;
            margin: 3px 0;
            padding: 3px 10px;
         }
         div.quote {
            background-color: wheat;
         }
         div.author-signature {
            border-top: 1px solid #CCCCCC;
            color: #333333;
            font-size: 0.929em;
            line-height: 140%;
            margin: 10px;
            padding: 3px 0;
         }
         div.header {
            margin-top: 5px;
            border-top: 1px solid brown;
            padding: 5px;
         }
         div.navbar li {
            padding: 5px;
         }
         div#top_link a {
            display: inline-block;
            padding: 0 5px;
         }
         div.header,
         div.navbar {
            background-color: #FFDAB9;
         }
         div.tabs ul li {
            display: inline-block;
            padding: 3px;
         }
         div.tabs ul li.active {
            background-color: pink;
         }
      </style>
   </head>
   <body style="background-color: #FFFFFF;">
      <div id="top_link">
         <?php echo $page_navbar ?>
      </div>
      <div id="article" class="content article">
         <?php echo $content; ?>
      </div>
      <?php include $tpl_path . '/page_header_footer.tpl.php'; ?>
      <script type='text/javascript'>
         setTimeout(function() {
            window.scrollTo(0, 1);
         }, 500);
      </script>
      <div>Copyright © 2009-2013 HoustonBBS.com. All rights reserved.</div>
   </body>
   <?php if ($domain === 'houstonbbs.com'): ?>
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
   <?php endif; ?>
</html>