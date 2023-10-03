<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>ECharts</title>
    <!-- Include the ECharts file you just downloaded -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.1/dist/echarts.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.2.0/chart.min.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.1/chart.min.js"></script>
</head>

<body>

<?php
$datas = json_decode($_GET['data']);
$daxis = [];
$ddata = [];
$dtarget = [];
foreach ($datas as $k => $v)
{
    $daxis[] = $v->period;
    $ddata[] = $v->total;
    $dtarget[] = 0;
}
?>
    <!-- Prepare a DOM with a defined width and height for ECharts -->
    <!-- <div id="main" style="width: 600px;height:200px;"></div> -->
    <div class="chart-container">
    <canvas id="chart"></canvas>
</div>
    <script type="text/javascript">
//         // Initialize the echarts instance based on the prepared dom
//         var myChart = echarts.init(document.getElementById('main'));

//         // Specify the configuration items and data for the chart
//         var option = {
//             title: { show: false },
//             label: {
//             show: false,
//             },legend:
// {
//     display: false
// },
//             tooltip: {},
//             legend: {},
//             xAxis: {
//                 data:  <?= json_encode($daxis); ?> //['Shirts', 'Cardigans', 'Chiffons', 'Pants', 'Heels', 'Socks']
//             },
//             yAxis: {
//                 splitLine: {
//                     show: true
//                 },
//                 scale: true
//             },
//             series: [{
//                 type: 'bar',
//                 data: <?= json_encode($ddata); ?>//[5, 20, 36, 10, 10, 20]
//             }]
//         };

//         // Display the chart using the configuration items and data just specified.
//         myChart.setOption(option);


        var data = {
  labels: <?= json_encode($daxis); ?>,
  datasets: [{
    label: "Total Piutang",
    backgroundColor: "rgba(255,99,132,0.2)",
    borderColor: "rgba(255,99,132,1)",
    borderWidth: 2,
    hoverBackgroundColor: "rgba(255,99,132,0.4)",
    hoverBorderColor: "rgba(255,99,132,1)",
    data: <?= json_encode($ddata); ?>,
  },
//   {
//     label: "Target",
//     backgroundColor: "rgba(132,99,255,0.2)",
//     borderColor: "rgba(132,99,255,1)",
//     borderWidth: 2,
//     hoverBackgroundColor: "rgba(132,99,255,0.4)",
//     hoverBorderColor: "rgba(132,99,255,1)",
//     data: <?= json_encode($dtarget); ?>,
//   }
    ]
};

var options = {
  maintainAspectRatio: false,
  scales: {
    y: {
    //   stacked: true,
      grid: {
        display: true,
        color: "rgba(255,99,132,0.2)"
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  },
  plugins: {
    legend: {
      display: false
    }
}
};

new Chart('chart', {
  type: 'bar',
  options: options,
  data: data
});
    </script>
</body>

</html>