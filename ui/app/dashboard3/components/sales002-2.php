<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<script type="text/javascript">
window.onload = function() {

var options = {
	// title: {
	// 	text: "Website Traffic Source"
	// },
	data: [{
			type: "pie",
			startAngle: 45,
			showInLegend: false,
			legendText: "{label}",
			indexLabel: "{label} ({y})",
			yValueFormatString:"#,##0.#"%"",
			dataPoints: [
				{ label: "Organic", y: 36 },
				{ label: "Email Marketing", y: 31 },
				{ label: "Referrals", y: 7 },
				{ label: "Twitter", y: 7 },
				{ label: "Facebook", y: 6 },
				{ label: "Google", y: 10 },
				{ label: "Others", y: 3 }
			]
	}]
};
$("#chartContainer").CanvasJSChart(options);

}
</script>
</head>
<body>
<div id="chartContainer" style="height: auto; width: 100%;"></div>
<script type="text/javascript" src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>  
<script type="text/javascript" src="https://cdn.canvasjs.com/jquery.canvasjs.min.js"></script>
</body>
</html>