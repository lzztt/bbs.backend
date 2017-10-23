<!--
You are free to copy and use this sample in accordance with the terms of the
Apache license (http://www.apache.org/licenses/LICENSE-2.0.html)
-->
<!--
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
   <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
   <title>
    Google Visualization API Sample
   </title>
   -->
   <script src="http://www.google.com/jsapi"></script>
   <script type="text/javascript">
    google.load('visualization', '1.1', {packages: ['corechart', 'controls']});
   </script>
   <script type="text/javascript">
    function drawVisualization() {
      var dashboard = new google.visualization.Dashboard(
         document.getElementById('dashboard'));

      var control = new google.visualization.ControlWrapper({
        'controlType': 'ChartRangeFilter',
        'containerId': 'control',
        'options': {
         // Filter by the date axis.
         'filterColumnIndex': 0,
         'ui': {
          'chartType': 'LineChart',
          'chartOptions': {
            'chartArea': {'width': '90%'},
            'hAxis': {'baselineColor': 'none'}
          },
          // Display a single series that shows the closing value of the stock.
          // Thus, this view has two columns: the date (axis) and the stock value (line series).
          'chartView': {
            'columns': [0, 1, 2, 3, 4]
          },
          // 1 day in milliseconds = 24 * 60 * 60 * 1000 = 86,400,000
          // 2 minutes = 2 * 60 * 1000
          'minRangeSize': 600000
         }
        },
        // Initial range: 2012-02-09 to 2012-03-20.
        'state': {'range': {'start': [0,0,0,0], 'end': [23,59,59,999999]}}
      });

      var chart = new google.visualization.ChartWrapper({
        'chartType': 'LineChart',
        'containerId': 'chart',
        'options': {
         // Use the same chart area width as the control for axis alignment.
         'chartArea': {'height': '80%', 'width': '88%'},
         'hAxis': {'slantedText': false},
         //'vAxis': {'viewWindow': {'min': 0, 'max': 180}},
         'title': 'Disk I/O requests per second',
         'legend': {'position': 'top'}
        },
        // Convert the first column from 'date' to 'string'.
        'view': {
         'columns': [
          {
            'calc': function(dataTable, rowIndex) {
             return dataTable.getFormattedValue(rowIndex, 0);
            },
            'type': 'string'
          }, 1, 2, 3, 4]
        }
      });

      var data = new google.visualization.DataTable();
      data.addColumn('timeofday', 'Time');
      data.addColumn('number', 'Jan 04');
      data.addColumn('number', 'Jan 05');
      data.addColumn('number', 'Jan 06');
      data.addColumn('number', 'Jan 07');



      // Create random stock values, just like it works in reality.
      data.addRows([
       <?php include '/tmp/tps_04-07_pick.stat'; ?>
      ]);

      dashboard.bind(control, chart);
      dashboard.draw(data, {curveType: "function"});
    }


    google.setOnLoadCallback(drawVisualization);
   </script>
   <!--
  </head>
  <body>
   -->
   <div id="dashboard">
      <div id="chart" style='width: 600px; height: 400px;'></div>
      <div id="control" style='width: 600px; height: 30px;'></div>
   </div>
   <!--
  </body>
</html>
   -->
