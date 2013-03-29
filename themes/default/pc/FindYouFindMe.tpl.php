<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh-hans" lang="zh-hans" dir="ltr">
   <head>

      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      </meta>

      <title>Find You Find Me 凡凡觅友</title>
      <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />

      <link type="text/css" rel="stylesheet" media="all" href="/themes/default/css/fyfm-min-1.2.css" />
      <script type="text/javascript" src="/themes/default/js/fyfm-min-1363914171.js"></script>
      <script type="text/javascript" src="https://www.google.com/jsapi"></script>
      <script type="text/javascript">

         google.load('visualization', '1.0', {'packages': ['corechart']});

         function drawChart(chartTitle, dataJSON, divID) {

            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Topping');
            data.addColumn('number', 'Slices');
            data.addRows(dataJSON);

            var options = {'title': chartTitle,
               'width': 400,
               'height': 300};

            var chart = new google.visualization.PieChart(document.getElementById(divID));
            chart.draw(data, options);
         }
      </script>

   </head>
   <body>
      <a href="/node/24037" id="activity" style="display: none; visibility: hidden;">4月6日(周六) 三十看从前</a>
      <div id="wrapper">
         <div class="b5" id="bar">
            <div class="b1"></div>
            <div class="b2"></div>
            <div class="b3"></div>
            <div class="b4"></div>
            <div class="b5"></div>
         </div>

         <div id="page">

            <div id="header">
               <?php echo $header; ?>
            </div>

            <div class="spacer"></div>

            <div id="content">
               <?php echo $content; ?>
            </div>
         </div>
      </div>
      <div id="footer">
         <?php echo $footer; ?>
      </div>


   </body>
</html>
