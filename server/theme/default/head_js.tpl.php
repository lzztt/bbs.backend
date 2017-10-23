<script>
  if ('querySelector' in document && 'localStorage' in window && 'addEventListener' in window) {
    document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"><\/script>');
  } else {
    document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"><\/script>');
  }
</script>
<script>
  if (!window.jQuery)
  {
    if ('querySelector' in document && 'localStorage' in window && 'addEventListener' in window) {
      document.write('<script src="//ajax.aspnetcdn.com/ajax/jQuery/jquery-2.1.0.min.js"><\/script>');
    }
    else {
      document.write('<script src="//ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.0.min.js"><\/script>');
    }
  }
</script>

<script>(typeof JSON === 'object') || document.write('<script src="//cdnjs.cloudflare.com/ajax/libs/json3/3.3.0/json3.min.js"><\/script>')</script>
<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7/html5shiv.min.js"></script><![endif]-->

<!--script src="/themes/<?php print $tpl_theme; ?>/js/min_1367737911.js"></script-->

<script src="/themes/<?php print $tpl_theme; ?>/js/jquery.cookie.js"></script>
<script src="/themes/<?php print $tpl_theme; ?>/js/jquery.upload-1.0.2.js"></script>
<script src="/themes/<?php print $tpl_theme; ?>/js/jquery.markitup.js"></script>
<script src="/themes/<?php print $tpl_theme; ?>/js/jquery.markitup.bbcode.set.js"></script>
<script src="/themes/<?php print $tpl_theme; ?>/js/hoverIntent.js"></script>
<script src="/themes/<?php print $tpl_theme; ?>/js/superfish.js"></script>
<script src="/themes/<?php print $tpl_theme; ?>/js/coin-slider.js"></script>
<script src="/themes/<?php print $tpl_theme; ?>/js/jquery.MetaData.js"></script>
<script src="/themes/<?php print $tpl_theme; ?>/js/jquery.rating.js"></script>
<script src="/themes/<?php print $tpl_theme; ?>/js/main.js"></script>