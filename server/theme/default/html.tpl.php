<!DOCTYPE html>
<html lang='zh' dir='ltr'>
   <head>
      <meta charset='UTF-8' />
      <meta name='description' content='<?php echo $head_description; ?>' />      
      <meta name='viewport' content='width=device-width, initial-scale=1' />

      <?php include $tpl_path . '/head_js.tpl.php'; ?>
      <?php include $tpl_path . '/head_css.tpl.php'; ?>

      <title><?php echo $head_title; ?></title>
      <link rel='apple-touch-icon' href='/apple-touch-icon.png' />
      <link rel='apple-touch-icon' sizes='72x72' href='/apple-touch-icon-72x72.png' />
      <link rel='apple-touch-icon' sizes='114x114' href='/apple-touch-icon-114x114.png' />
   </head>
   <body>
      <div id='page_overlay'><div id='popup'></div><div id='popup_bg'></div></div>
        <div id='page_header'>
         <div id='page_header_inner'>
            <div data-umode='<?php echo $umode_pc; ?>' style='position:relative; height:150px'>
               <div id='logo-title'>
                  <div id='logo'><a style='padding: 0pt; margin: 0pt; display: block; width: 60px; height: 60px;' href='/' title='首页' rel='home'><img src='/themes/default/images/logo_60x60.png' alt='首页' id='logo-image'></a></div>
                  <div id='site-name'><span style='color: #A0522D;'>缤纷休斯顿</span></div>
                  <div id='site-slogan'><span style='color: #32CD32;'>We share</span><span style='color: #A0522D;'> - </span><span style='color: #1E90FF;'>We care</span><span style='color: #A0522D;'> - </span><span style='color: #B22222;'>We inspire</span></div>
               </div>
               <?php include $tpl_path . '/head_ad.tpl.php'; ?>
            </div>
            <?php echo $page_navbar; ?>
            <div style="clear:both;"></div>
         </div>
      </div>
      <div id='page_body'>
         <div id='page_body_inner'>
            <?php echo $content; ?>
         </div>
         <div style="clear:both;"></div>
      </div>
      <div id='page_navbar_mobile' data-umode='<?php echo $umode_mobile; ?>'><div>页面结束，以下为站内快捷链接：</div></div>
      <div id='page_footer'>
         <div id='page_footer_inner'>
            <div id='copyright'>Contact the Web Administrator at
               <span class='highlight'>admin@houstonbbs.com</span> | Copyright © 2009-2013 HoustonBBS.com. All rights reserved. | <a href='/term'>Terms and Conditions</a>
            </div>
         </div>
         <div style="clear:both;"></div>
      </div>
      <div id='page_data' style='display:none;'><?php echo $page_data; ?></div>
   </body>
   <?php if ( $domain === 'houstonbbs.com' ): ?>
      <script>
         (function(i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function() {
               (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o), m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
         })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

         ga('create', 'UA-36671672-1', 'houstonbbs.com');
         ga('send', 'pageview');
      </script>
   <?php endif; ?>
</html>
