<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" lang="en" itemscope itemtype="http://schema.org/WebPage">
  <head>

		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

		<!-- All Search Engines -->
		<meta name="robots" content="noindex,nofollow" />
		<!-- Google Specific -->
		<meta name="googlebot" content="noindex,nofollow" />

		<!-- tell Google not to translate this page -->
		<meta name="google" content="notranslate" />

		<!-- DNS prefetch -->
		<link rel="dns-prefetch" href="//code.highcharts.com" />

		<!-- external stylesheets  -->
		<link rel="stylesheet" href="https://necolas.github.io/normalize.css/8.0.0/normalize.css" />

		<!-- internal stylesheets -->

		<!-- hacky inline stylesheets -->
    <style type="text/css">

    	#chart {
      	width: 100%;
				height: 100vh;
    	}

    </style>

		<!-- general page information -->
		<title>Solar</title>

		<!-- JS external -->
		<script src="https://code.highcharts.com/highcharts.js"></script>
		<script src="https://code.highcharts.com/modules/exporting.js"></script>

		<!-- JS internal -->

    <script>

    function returnSum(arr) {
			return Math.round(arr.reduce(function(pv, cv) {
				return pv + cv;
			}, 0));
		}

    <?php

		require "db.php";

		$conn = new mysqli($host, $user, $pw, $db);

		if(!isset($_GET["lastDays"])) {
			$queryData = "SELECT * FROM `data` ORDER BY `ts` ASC";
			$range = "Gesamtdaten";
		} else if(isset($_GET["lastDays"])) {

			$threshold = time('now') - $_GET["lastDays"] * 86400;
			$queryData = "SELECT * FROM `data` WHERE `ts` >= " .$threshold. " ORDER BY `ts` ASC";

			$range = "Daten der letzten " .$_GET["lastDays"]. " Tage";
		}


    $getData = $conn->query($queryData);

    $data = [
			"ts" => [],
			"production" => [],
			"purchased" => [],
			"feedin" => [],
			"consumption" => [],
			"selfconsumption" => [],
		];

    if($getData->num_rows > 0) {
      while($dataset = $getData->fetch_assoc()) {

				foreach($dataset as $name => $subArray) {
					array_push($data[$name], $subArray);
				}
      }
    }

		foreach($data as $name => $subArray) {

			if($name != "ts") {
				echo '
					const ' .$name. ' = ' .json_encode($subArray, JSON_NUMERIC_CHECK). '
				';
			} else if($name == "ts") {
				echo '
				var tempArray = ' .json_encode($subArray, JSON_NUMERIC_CHECK). '
				const ts = [];

				tempArray.forEach(function(timestamp) {
					var date = new Date(timestamp * 1000);
					ts.push(date);
				});
				';
			}
		}

		?>

    </script>

  </head>

	<body>

    <div id="chart"></div>

		<script>

		(function() {
			Highcharts.chart("chart", {
				chart: {
					type: "spline",
					zoomType: "x"
				},
				title: {
					useHTML: true,
					text: "Solarübersicht"
				},
				subtitle: {
					text: <?php echo '"' .$range. '"'; ?>
				},
				xAxis: {
					categories: ts
				},
				yAxis: {
					title: {
						text: "Value"
					},
					labels: {
						formatter: function () {
							return this.value.toLocaleString("en-US");
						}
					}
				},
				legend: {
					enabled: true
				},
				tooltip: {
					crosshairs: true,
					shared: true
				},
				plotOptions: {
					spline: {
						marker: {
							radius: 4,
							lineColor: "#666666",
							lineWidth: 1
						}
					}
				},
				series: [{
					name: "Production (SUM: " + returnSum(production).toLocaleString("en-US") + ")",
					marker: {
						symbol: "triangle"
					},
					color: "darkgreen",
					data: production
				}, {
					name: "FeedIn (SUM: " + returnSum(feedin).toLocaleString("en-US") + ")",
					marker: {
						symbol: "square"
					},
					color: "yellowgreen",
					data: feedin,
					visible: false
				}, {
					name: "Purchased (SUM: " + returnSum(purchased).toLocaleString("en-US") + ")",
					marker: {
						symbol: "diamond"
					},
					color: "red",
					data: purchased,
					visible: false
				}, {
					name: "Consumption (SUM: " + returnSum(consumption).toLocaleString("en-US") + ")",
					marker: {
						symbol: "cross"
					},
					color: "orange",
					data: consumption
				}, {
					name: "SelfConsumption (SUM: " + returnSum(selfconsumption).toLocaleString("en-US") + ")",
					marker: {
						symbol: "triangle-down"
					},
					color: "purple",
					data: selfconsumption,
					visible: false
				}]
			});
		})();

		</script>

	</body>

	</html>
