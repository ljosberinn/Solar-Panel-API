<?php

require "db.php";

if(isset($_GET["year"]) && isset($_GET["month"])) {
  $year = $_GET["year"];
  $month = $_GET["month"];

  if(is_numeric($year) && is_numeric($month)) {
	   $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
  } else {
    die();
  }

  $url = "https://monitoringapi.solaredge.com/site/319863/powerDetails.json?startTime=" .$year. "-" .$month. "-1%200:0:0&endTime=" .$year. "-" .$month. "-" .$days_in_month. "%200:0:0&api_key=" .$key;
} else {

  $today = date("Y-n-j");
  $lastHour = date("G") - 1;

  $url = "https://monitoringapi.solaredge.com/site/319863/powerDetails.json?startTime=" .$today. "%20" .$lastHour. ":0:0&endTime=" .$today. "%20" .$lastHour. ":59:0&api_key=" .$key;
}

$arrContextOptions = [
 'ssl' => [
   'verify_peer' => false,
   'verify_peer_name' => false
 ]
];

$data = file_get_contents($url, false, stream_context_create($arrContextOptions));
$decoded_data = json_decode($data, true);

$data = $decoded_data["powerDetails"]["meters"];

$conn = new mysqli($host, $user, $pw, $db);

$arrayPosition = 0;

$query = "INSERT INTO `data` (`ts`, `production`, `purchased`, `feedin`, `consumption`, `selfconsumption`) VALUES";

foreach($data[0]["values"] as $selfConsumption) {

  $selfConsumptionValue = $selfConsumption["value"];
  $consumptionValue = $data[1]["values"][$arrayPosition]["value"];
  $purchasedValue = $data[2]["values"][$arrayPosition]["value"];
  $productionValue = $data[3]["values"][$arrayPosition]["value"];
  $feedInValue = $data[4]["values"][$arrayPosition]["value"];

  $d = new DateTime($selfConsumption["date"], new DateTimeZone('Europe/Rome'));
  $ts = $d->getTimestamp();

  if(gettype($productionValue) == "NULL") {
    $productionValue = 0;
  }

  if(gettype($selfConsumptionValue) == "NULL") {
    $selfConsumptionValue = 0;
  }

  $query .= "(" .$ts. ", " .$productionValue. ", " .$purchasedValue. ", " .$feedInValue. ", " .$consumptionValue. ", " .$selfConsumptionValue. "), ";

  $arrayPosition++;
}

$query = substr($query, 0, -2);
$conn->query($query);

if(isset($_GET["year"]) && isset($_GET["month"])) {
  echo "done with " .$year. "-" .$month;
} else {
  echo "done with " .$today;
}


?>
