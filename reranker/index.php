<?php
include_once 'formatting.php';
include_once 'processing.php';
include_once 'fetching.php';
include_once 'classes/RerankParams.php';
set_time_limit(0);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>YouTube metadata re-ranking</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
          integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>

    <!--    Slider -->
    <script src="js/bootstrap-slider.js"></script>
    <link rel="stylesheet" type="text/css" href="css/bootstrap-slider.css">

    <link rel="stylesheet" type="text/css" href="css/customstyle.css">
</head>
<body>

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">VMM YouTube reranker</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">Home</a></li>
                <li><a href="#about">About</a></li>
                <?php
                // TODO: vyplnit někam sekci About (třeba na konec stránky) a poslat tam odkaz, aby se neřeklo.
                ?>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<div class="container">

    <!-- FORM INPUT -->
    <form action="index.php">
        <div class="form-group">
            <label for="query">Search query:</label>
            <input type="text" class="form-control" id="query" name="query"
                   value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="orig-pos-weight">Original position weight:</label>
            <input id="orig-pos-weight" data-slider-id='orig-pos-weight' type="text"
                   data-slider-min="0" data-slider-max="100"
                   data-slider-step="1" data-slider-value="25" data-slider-tooltip="hide">
            <span id="ex6CurrentSliderValLabel"><span id="orig-pos-weight-val">0</span></span>
        </div>

        <?php
        // TODO: radiobuttony a šoupátka pro další parametry (délka videa, datum atd.)
        ?>

        <button type="submit" class="btn btn-default">Search</button>
    </form>


    <script>
        // Slider value handling - set initial value and listen for slide events
        var slider = new Slider("#orig-pos-weight");
        document.getElementById("orig-pos-weight-val").textContent = slider.element.value + "%";
        slider.on("slide", function (slideEvt) {
            document.getElementById("orig-pos-weight-val").textContent = slideEvt + "%";
        });
    </script>

    <div class="row row-margin">
        <?php
        if (isset($_GET["query"]) && "" != trim($_GET["query"])) {
            $query = $_GET["query"];
            echo "<h3>Search results for: " . $query . "</h3>";

            $resultCollection = fetchSearchResult($query, false, 100);

            $params = new RerankParams();
            $params->setTudRatioWeight(1);
            $params->setTudRatioRequested(0.1);

            $rerankedCollection = rerankResultCollection($resultCollection, $params);
            ?>
            <div class="col-md-6">
                <h4>Original results</h4>
                <p>
                    <?php
                    printSimpleOutput($resultCollection, false);
                    ?>
                </p>
            </div>
            <div class="col-md-6">
                <h4>Reranked results</h4>
                <p>
                    <?php
                    printSimpleOutput($rerankedCollection, true);
                    ?>
                </p>
            </div>
            <?php
        } else {
            echo "<h3>No search results</h3>";
        }
        ?>


    </div>
    <div class="row row-margin">
        <h3>Debugging output</h3>
        <?php
        echo "<pre>";
        //                $pokus = fetchSearchResult($_GET["query"], true, 1000);
        echo "</pre>";
        ?>
    </div>
</div>

<!-- Bootstrap core JavaScript
    ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="../../dist/js/bootstrap.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>
</body>
</html>
