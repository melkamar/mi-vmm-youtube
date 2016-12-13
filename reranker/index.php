<?php
include_once 'formatting.php';
include_once 'processing.php';
include_once 'fetching.php';
include_once 'classes/RerankParams.php';
erase_log();
//set_time_limit(0);
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

    <!-- jQuery CSS -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- Latest compiled and minified JavaScript -->
    <!--    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"-->
    <!--            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"-->
    <!--            crossorigin="anonymous"></script>-->

    <!--    Google Map Picker -->
    <script src="js/if_gmap.js"></script>
    <script type="text/javascript"
            src="http://maps.google.com/maps/api/js?key=AIzaSyBA7vgbuOrs7GJl2gIHvmttVEtT5u6PG1w&sensor=false"></script>

    <!--    Slider -->
    <script src="js/bootstrap-slider.js"></script>
    <link rel="stylesheet" type="text/css" href="css/bootstrap-slider.css">

    <link rel="stylesheet" type="text/css" href="css/customstyle.css">
</head>
<body onload="if_gmap_init();">

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

<?php
// Parse parameters

$params = new RerankParams();

$params->setOriginalPositionWeight(get_query_param($_GET, 'origposweight', 25));
$params->setDurationWeight(get_query_param($_GET, 'durationweight', 25));
$params->setDatePublishedWeight(get_query_param($_GET, 'dateweight', 25));
$params->setGpsWeight(get_query_param($_GET, 'gpsweight', 0));
$params->setViewsWeight(get_query_param($_GET, 'viewsweight', 25));
$params->setTudRatioWeight(get_query_param($_GET, 'tudweight', 25));
$params->setAuthorNameWeight(get_query_param($_GET, 'authorweight', 25));
$params->setDurationRequested(intval(get_query_param($_GET, 'durationhours', null)) * 60 * 60 +
    intval(get_query_param($_GET, 'durationminutes', null)) * 60 +
    intval(get_query_param($_GET, 'durationseconds', null)));
$params->setDatePublishedRequested(strtotime(get_query_param($_GET, 'date', null)));

$gpsLatitude = get_query_param($_GET, 'gpslatitude', '50.104423');
$gpsLongitude = get_query_param($_GET, 'gpslongitude', '14.388732');
$params->setGpsRequested(
    array(
        "latitude" => $gpsLatitude,
        "longitude" => $gpsLongitude
    )
);

$params->setViewsRequested(get_query_param($_GET, 'viewscount', null));
$params->setTudRatioRequested(get_query_param($_GET, 'tudratio', null));
$params->setAuthorNameRequested(get_query_param($_GET, 'authorname', null));
$params->setAuthorNameCaseSensitive(false);
?>

<div class="container">

    <!-- FORM INPUT -->
    <form action="index.php">
        <div class="form-group">
            <label for="query">Search query:</label>
            <input type="text" class="form-control" id="query" name="query"
                   value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="videos-count">Number of videos</label>
            <input id="videos-count" data-slider-id='videos-count' type="text"
                   data-slider-min="50" data-slider-max="500"
                   data-slider-step="50"
                   data-slider-value="<?php print_query_param($_GET, 'videoscount', '100') ?>"
                   data-slider-tooltip="hide" class="slider">
            <span id="videos-count-val-label">0</span>
            <input type="hidden" id="videos-count-val" name="videoscount"
                   value="<?php print_query_param($_GET, 'videoscount', '100') ?>">
        </div>

        <div class="form-group">
            <label for="orig-pos-weight">Original position weight:</label>
            <input id="orig-pos-weight" data-slider-id='orig-pos-weight' type="text"
                   data-slider-min="0" data-slider-max="100"
                   data-slider-step="1"
                   data-slider-value="<?php print_query_param($_GET, 'origposweight', '30') ?>"
                   data-slider-tooltip="hide" class="slider">
            <span id="orig-pos-weight-val-label">0</span>
            <input type="hidden" id="orig-pos-weight-val" name="origposweight"
                   value="<?php print_query_param($_GET, 'origposweight', '30') ?>">
        </div>

        <div class="panel panel-default form-group">
            <div class="panel-heading">
                GPS coordinates
                <button type="button" class="btn btn-link btn-sm" data-toggle="collapse" data-target="#GPScollapse">Show/Hide</button>
            </div>
            <div class="panel-body collapse in" id="GPScollapse">
                <div class="row">
                    <!-- Map -->
                    <div id="maparea" class="col-md-6">
                        <div id="mapitems" style="width: 480px; height: 240px"></div>
                    </div>

                    <!-- Buttons (right section) -->
                    <div class="col-md-6">
                        <div class="row">
                            <label for="longval">Longitude: </label>
                            <input type=text class="form-control"
                                   value="<?php print_query_param($_GET, 'gpslongitude', '14.388732'); ?>"
                                   id="longval" name="gpslongitude">
                        </div>

                        <div class="row">
                            <label for="longval" class="top-buffer">Latitude: </label>
                            <input type=text class="form-control"
                                   value="<?php echo isset($_GET['gpslatitude']) ? htmlspecialchars($_GET['gpslatitude']) : '50.104423'; ?>"
                                   id="latval" name="gpslatitude">
                        </div>
                        <div class="row">
                            <input type=button class="btn btn-default top-buffer" value="Jump to location"
                                   onclick="if_gmap_loadpicker();">
                        </div>

                        <div class="row top-buffer">
                            <div class="form-group">
                                <label for="gps-weight">Position weight:</label>
                                <input id="gps-weight" data-slider-id='gps-weight' type="text"
                                       data-slider-min="0" data-slider-max="100"
                                       data-slider-step="1"
                                       data-slider-value="<?php print_query_param($_GET, 'gpsweight', '25') ?>"
                                       data-slider-tooltip="hide" class="slider">
                                <span id="gps-weight-val-label">0</span>
                                <input type="hidden" id="gps-weight-val" name="gpsweight"
                                       value="<?php print_query_param($_GET, 'gpsweight', '25') ?>">
                            </div>
                        </div>
                    </div> <!-- end of right section -->
                </div>
            </div>
            <div class="row"></div>
        </div> <!-- end of GPS panel -->

        <!--    Video duration form    -->
        <div class="panel panel-default form-group">
            <div class="panel-heading">
                Duration
                <button type="button" class="btn btn-link btn-sm" data-toggle="collapse" data-target="#DurationCollapse">Show/Hide</button>
            </div>
            <div class="panel-body collapse" id="DurationCollapse">
                <div class="row">
                    <div class="col-md-2">
                        <label for="durationhours">Hours:</label>
                        <input type=text class="form-control"
                               value="<?php print_query_param($_GET, 'durationhours', ''); ?>"
                               id="durationhours" name="durationhours">
                    </div>
                    <div class="col-md-2">
                        <label for="durationminutes">Minutes:</label>
                        <input type=text class="form-control"
                               value="<?php print_query_param($_GET, 'durationminutes', ''); ?>"
                               id="durationminutes" name="durationminutes">
                    </div>
                    <div class="col-md-2">
                        <label for="durationseconds">Seconds:</label>
                        <input type=text class="form-control"
                               value="<?php print_query_param($_GET, 'durationseconds', ''); ?>"
                               id="durationseconds" name="durationseconds">
                    </div>
                    <div class="col-md-1"></div>
                    <div class="form-group col-md-4">
                        <label for="duration-weight">Duration weight:</label>
                        <input id="duration-weight" data-slider-id='duration-weight' type="text"
                               data-slider-min="0" data-slider-max="100"
                               data-slider-step="1"
                               data-slider-value="<?php print_query_param($_GET, 'durationweight', '25') ?>"
                               data-slider-tooltip="hide" class="slider">
                        <span id="duration-weight-val-label">0</span>
                        <input type="hidden" id="duration-weight-val" name="durationweight"
                               value="<?php print_query_param($_GET, 'durationweight', '25') ?>">
                    </div>
                </div>
            </div>
        </div> <!-- End of Duration form -->

        <!--    Views form    -->
        <div class="panel panel-default form-group">
            <div class="panel-heading">
                Views
                <button type="button" class="btn btn-link btn-sm" data-toggle="collapse" data-target="#ViewsCollapse">Show/Hide</button>
            </div>
            <div class="panel-body collapse" id="ViewsCollapse">
                <div class="row">
                    <div class="col-md-2">
                        <input type=text class="form-control"
                               value="<?php print_query_param($_GET, 'viewscount', ''); ?>"
                               id="viewscount" name="viewscount">
                    </div>
                    <div class="col-md-1"></div>
                    <div class="form-group col-md-4">
                        <label for="views-weight">Views weight:</label>
                        <input id="views-weight" data-slider-id='views-weight' type="text"
                               data-slider-min="0" data-slider-max="100"
                               data-slider-step="1"
                               data-slider-value="<?php print_query_param($_GET, 'viewsweight', '25') ?>"
                               data-slider-tooltip="hide" class="slider">
                        <span id="views-weight-val-label">0</span>
                        <input type="hidden" id="views-weight-val" name="viewsweight"
                               value="<?php print_query_param($_GET, 'viewsweight', '25') ?>">
                    </div>
                </div>
            </div>
        </div> <!-- End of Views form -->


        <!--    TUD form    -->
        <div class="panel panel-default form-group">
            <div class="panel-heading">
                Thumbs up/down ratio
                <button type="button" class="btn btn-link btn-sm" data-toggle="collapse" data-target="#TUDCollapse">Show/Hide</button>
            </div>
            <div class="panel-body collapse" id="TUDCollapse">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="tud-ratio">Thumbs up/down ratio:</label>
                        <input id="tud-ratio" data-slider-id='tud-ratio' type="text"
                               data-slider-min="0" data-slider-max="1"
                               data-slider-step="0.01"
                               data-slider-value="<?php print_query_param($_GET, 'tudratio', '0') ?>"
                               data-slider-tooltip="hide" class="slider">
                        <span id="tud-ratio-val-label">0</span>
                        <input type="hidden" id="tud-ratio-val" name="tudratio"
                               value="<?php print_query_param($_GET, 'tudratio', '0') ?>">
                    </div>

                    <div class="col-md-1"></div>
                    <div class="form-group col-md-4">
                        <label for="tud-weight">Thumbs up/down weight:</label>
                        <input id="tud-weight" data-slider-id='tud-weight' type="text"
                               data-slider-min="0" data-slider-max="100"
                               data-slider-step="1"
                               data-slider-value="<?php print_query_param($_GET, 'tudweight', '25') ?>"
                               data-slider-tooltip="hide" class="slider">
                        <span id="tud-weight-val-label">0</span>
                        <input type="hidden" id="tud-weight-val" name="tudweight"
                               value="<?php print_query_param($_GET, 'tudweight', '25') ?>">
                    </div>
                </div>
            </div>
        </div> <!-- End of TUD form -->

        <!--    Author form    -->
        <div class="panel panel-default form-group">
            <div class="panel-heading">
                Author name
                <button type="button" class="btn btn-link btn-sm" data-toggle="collapse" data-target="#AuthorCollapse">Show/Hide</button>
            </div>
            <div class="panel-body collapse" id="AuthorCollapse">
                <div class="row">
                    <div class="col-md-4">
                        <input type=text class="form-control"
                               value="<?php print_query_param($_GET, 'authorname', ''); ?>"
                               id="authorname" name="authorname">
                    </div>
                    <div class="col-md-1"></div>
                    <div class="form-group col-md-4">
                        <label for="author-weight">Author name weight:</label>
                        <input id="author-weight" data-slider-id='author-weight' type="text"
                               data-slider-min="0" data-slider-max="100"
                               data-slider-step="1"
                               data-slider-value="<?php print_query_param($_GET, 'authorweight', '25') ?>"
                               data-slider-tooltip="hide" class="slider">
                        <span id="author-weight-val-label">0</span>
                        <input type="hidden" id="author-weight-val" name="authorweight"
                               value="<?php print_query_param($_GET, 'authorweight', '25') ?>">
                    </div>
                </div>
            </div>
        </div> <!-- End of Author form -->

        <!--    Date form    -->
        <div class="panel panel-default form-group">
            <div class="panel-heading">
                Date published
                <button type="button" class="btn btn-link btn-sm" data-toggle="collapse" data-target="#DateCollapse">Show/Hide</button>
            </div>
            <div class="panel-body collapse" id="DateCollapse">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" id="datepicker" name="date" class="form-control"
                               value="<?php print_query_param($_GET, 'date', ''); ?>">
                    </div>
                    <div class="col-md-1"></div>
                    <div class="form-group col-md-4">
                        <label for="date-weight">Date published weight:</label>
                        <input id="date-weight" data-slider-id='date-weight' type="text"
                               data-slider-min="0" data-slider-max="100"
                               data-slider-step="1"
                               data-slider-value="<?php print_query_param($_GET, 'dateweight', '25') ?>"
                               data-slider-tooltip="hide" class="slider">
                        <span id="date-weight-val-label">0</span>
                        <input type="hidden" id="date-weight-val" name="dateweight"
                               value="<?php print_query_param($_GET, 'dateweight', '25') ?>">
                    </div>
                </div>
            </div>
        </div> <!-- End of Date form -->

        <button type="submit" class="btn btn-default">Search</button>
    </form>


    <script>
        // Slider value handling - set initial value and listen for slide events
        var slider = new Slider("#orig-pos-weight");
        document.getElementById("orig-pos-weight-val-label").textContent = slider.element.value + "%";
        slider.on("slide", function (slideEvt) {
            document.getElementById("orig-pos-weight-val-label").textContent = slideEvt + "%";
            document.getElementById("orig-pos-weight-val").value = slideEvt;
        });


        var slider = new Slider("#gps-weight");
        document.getElementById("gps-weight-val-label").textContent = slider.element.value + "%";
        slider.on("slide", function (slideEvt) {
            document.getElementById("gps-weight-val-label").textContent = slideEvt + "%";
            document.getElementById("gps-weight-val").value = slideEvt;
        });

        var slider = new Slider("#duration-weight");
        document.getElementById("duration-weight-val-label").textContent = slider.element.value + "%";
        slider.on("slide", function (slideEvt) {
            document.getElementById("duration-weight-val-label").textContent = slideEvt + "%";
            document.getElementById("duration-weight-val").value = slideEvt;
        });

        var slider = new Slider("#views-weight");
        document.getElementById("views-weight-val-label").textContent = slider.element.value + "%";
        slider.on("slide", function (slideEvt) {
            document.getElementById("views-weight-val-label").textContent = slideEvt + "%";
            document.getElementById("views-weight-val").value = slideEvt;
        });

        // Thumbs UP/DOWN
        var slider = new Slider("#tud-ratio");
        document.getElementById("tud-ratio-val-label").textContent = slider.element.value + "%";
        slider.on("slide", function (slideEvt) {
            document.getElementById("tud-ratio-val-label").textContent = slideEvt + "%";
            document.getElementById("tud-ratio-val").value = slideEvt;
        });
        var slider = new Slider("#tud-weight");
        document.getElementById("tud-weight-val-label").textContent = slider.element.value;
        slider.on("slide", function (slideEvt) {
            document.getElementById("tud-weight-val-label").textContent = slideEvt;
            document.getElementById("tud-weight-val").value = slideEvt;
        });

        var slider = new Slider("#author-weight");
        document.getElementById("author-weight-val-label").textContent = slider.element.value + "%";
        slider.on("slide", function (slideEvt) {
            document.getElementById("author-weight-val-label").textContent = slideEvt + "%";
            document.getElementById("author-weight-val").value = slideEvt;
        });

        var slider = new Slider("#date-weight");
        document.getElementById("date-weight-val-label").textContent = slider.element.value + "%";
        slider.on("slide", function (slideEvt) {
            document.getElementById("date-weight-val-label").textContent = slideEvt + "%";
            document.getElementById("date-weight-val").value = slideEvt;
        });

        var slider = new Slider("#videos-count");
        document.getElementById("videos-count-val-label").textContent = slider.element.value;
        slider.on("slide", function (slideEvt) {
            document.getElementById("videos-count-val-label").textContent = slideEvt;
            document.getElementById("videos-count-val").value = slideEvt;
        });
    </script>


    <div class="row row-margin">
        <?php
        if (isset($_GET["query"]) && "" != trim($_GET["query"])) {
            $query = $_GET["query"];
            echo "<h3>Search results for: " . $query . "</h3>";

            $resultCollection = fetchSearchResult($query, get_query_param($_GET, 'videoscount', '100'));

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
        //        var_dump(scandir(getcwd()));
        //        var_dump(file_get_contents("log.log"));
        echo "</pre>";
        ?>
    </div>
</div>

<!-- Bootstrap core JavaScript
    ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    $(function () {
        $("#datepicker").datepicker({dateFormat: "dd.mm.yy"});
    });
</script>
</body>
</html>
