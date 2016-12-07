<?php
include_once 'processing.php';
include_once 'classes/Video.php';
include_once 'classes/RerankParams.php';
set_time_limit(0);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
<?php
//    $arr = array();
//    array_push($arr, new Video());

erase_log();
$query = "strahov";
$resultCollection = fetchSearchResult($query, false, 5);

$params = new RerankParams();

$params->setOriginalPositionWeight(0);

//$params->setDurationWeight(0.9);
//$params->setDurationRequested(247);

//$params->setDatePublishedWeight(0.7);
//$params->setDatePublishedRequested(strtotime("now"));
//$params->setDatePublishedRequested(1478473200);

//$params->setViewsWeight(1);
//$params->setViewsRequested(272890);

//$params->setGpsWeight(1); // GPS na strahov
//$params->setGpsRequested(
//    array(
//        "latitude" => 50.081062,
//        "longitude" => 14.392563
//    ));

//$params->setAuthorNameWeight(1);
//$params->setAuthorNameCaseSensitive(false);
//$params->setAuthorNameRequested("dom");

$params->setTudRatioWeight(1);
$params->setTudRatioRequested(0.1);


rerankResultCollection($resultCollection, $params)
?>

Hello.
</body>
