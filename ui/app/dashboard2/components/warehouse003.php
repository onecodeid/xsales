<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>ECharts</title>
    <!-- Include the ECharts file you just downloaded -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.1/dist/echarts.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.2.0/chart.min.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.1/chart.min.js"></script>
    <script src="../../../assets/js/numeral.min.js"></script>
</head>

<body>

<?php
$datas = json_decode($_GET['data']);
$labels = [];
$amounts = [];
foreach ($datas as $k => $v)
{
    $labels[] = $v->name;
    $amounts[] = $v->amount;
}
?>
    <!-- Prepare a DOM with a defined width and height for ECharts -->
    <!-- <div id="main" style="width: 600px;height:200px;"></div> -->
    <div class="chart-container">
    <canvas id="chart-container" style="height:200px;width:200px"></canvas>
</div>
<style>
    #chart-container {
  height: 100% !important; /* set the height of the canvas to match the height of the container */
  width: 100% !important;
}
</style>
    <script type="text/javascript">
var chartData = {
  labels: <?=json_encode($labels);?>,
  datasets: [{
    fill: true,
    data: <?=json_encode($amounts);?>,
    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
  }]
};

var chartOptions = {
//   title: {
//     display: true,
//     text: 'Pie Chart'
//   },
    indexAxis: 'y',
    // Elements options apply to all of the options unless overridden in a dataset
    // In this case, we are setting the border of each horizontal bar to be 2px wide
    elements: {
      bar: {
        borderWidth: 2,
      }
    },

  plugins: {
    
    responsive: true,
    legend: {
        display: false,
        position: 'bottom',
    },
    title: {
    display: false,
    text: 'Chart.js Horizontal Bar Chart'
    }
},
maintainAspectRatio: false
};

var ctx = document.getElementById('chart-container').getContext('2d');
var chart = new Chart(ctx, {
  type: 'bar',
  data: chartData,
  options: chartOptions
});




    </script>
</body>

</html>