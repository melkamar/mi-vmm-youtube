<?php
/**
 * Created by IntelliJ IDEA.
 * User: Martin
 * Date: 30.11.2016
 * Time: 20:25
 */

include_once 'core.php';
include_once 'util.php';
include_once 'normalizing.php';

/**
 * @param \Video[] $resultCollection Array of \Video objects to be reranked.
 * @param \RerankParams $params Settings for reranking - weights, requested values.
 * @return \Video[] Reranked array of \Video objects.
 */
function rerankResultCollection($resultCollection, $params)
{
    debug_log("_________________________________________________");
    debug_log("________  RERANKING ITEMS _______________________");
    debug_log("_________________________________________________");

    // Calculate non-normalized distances for given videos,
    // keep track of maxims to later rerank.
    /** @var \MetaVideo[] $metaVideos */
    $metaVideos = array();
    $maxOriginalPosition = null;
    $maxDurationDistance = null;
    $maxDatePublishedDistance = null;
    $maxGpsDistance = null;
    $maxViewsDistance = null;
    $maxTudRatioDistance = null;
    $maxAuthorNameDistance = null;

    foreach ($resultCollection as $video) {
        debug_log(">>> Processing video " . $video->getId());

        $metaVideo = new MetaVideo($video);
        $metaVideo->setDurationDistance(calcDurationDistance($video, $params));
        $metaVideo->setDatePublishedDistance(calcDatePublishedDistance($video, $params));
        $metaVideo->setGpsDistance(calcGpsDistance($video, $params));
        $metaVideo->setViewsDistance(calcViewsDistance($video, $params));
        $metaVideo->setTudRatioDistance(calcTudRatioDistance($video, $params));
        $metaVideo->setAuthorNameDistance(calcAuthorNameDistance($video, $params));

        if ($maxOriginalPosition === null) {
            $maxOriginalPosition = $metaVideo->getOriginalPosition();
        } else {
            if ($metaVideo->getOriginalPosition() !== null && $metaVideo->getOriginalPosition() > $maxOriginalPosition)
                $maxOriginalPosition = $metaVideo->getOriginalPosition();
        }

        if ($maxDurationDistance === null) {
            $maxDurationDistance = $metaVideo->getDurationDistance();
        } else {
            if ($metaVideo->getDurationDistance() !== null && $metaVideo->getDurationDistance() > $maxDurationDistance)
                $maxDurationDistance = $metaVideo->getDurationDistance();
        }

        if ($maxDatePublishedDistance === null) {
            $maxDatePublishedDistance = $metaVideo->getDatePublishedDistance();
        } else {
            if ($metaVideo->getDatePublishedDistance() !== null && $metaVideo->getDatePublishedDistance() > $maxDatePublishedDistance)
                $maxDatePublishedDistance = $metaVideo->getDatePublishedDistance();
        }

        if ($maxGpsDistance === null) {
            $maxGpsDistance = $metaVideo->getGpsDistance();
        } else {
            if ($metaVideo->getGpsDistance() !== null && $metaVideo->getGpsDistance() > $maxGpsDistance)
                $maxGpsDistance = $metaVideo->getGpsDistance();
        }

        if ($maxViewsDistance === null) {
            $maxViewsDistance = $metaVideo->getViewsDistance();
        } else {
            if ($metaVideo->getViewsDistance() !== null && $metaVideo->getViewsDistance() > $maxViewsDistance)
                $maxViewsDistance = $metaVideo->getViewsDistance();
        }

        if ($maxTudRatioDistance === null) {
            $maxTudRatioDistance = $metaVideo->getTudRatioDistance();
        } else {
            if ($metaVideo->getTudRatioDistance() !== null && $metaVideo->getTudRatioDistance() > $maxTudRatioDistance)
                $maxTudRatioDistance = $metaVideo->getTudRatioDistance();
        }

        if ($maxAuthorNameDistance === null) {
            $maxAuthorNameDistance = $metaVideo->getAuthorNameDistance();
        } else {
            if ($metaVideo->getAuthorNameDistance() !== null && $metaVideo->getAuthorNameDistance() > $maxAuthorNameDistance)
                $maxAuthorNameDistance = $metaVideo->getAuthorNameDistance();
        }


        array_push($metaVideos, $metaVideo);

        debug_log("");
        debug_log("");
    }

    // Normalize using the obtained maxims
    debug_log("");
    debug_log(">>> Normalizing...");

    $maxims = new DistanceMaxims($maxOriginalPosition,
        $maxDurationDistance,
        $maxDatePublishedDistance,
        $maxGpsDistance,
        $maxViewsDistance,
        $maxTudRatioDistance,
        $maxAuthorNameDistance);

    normalize($metaVideos, $maxims);
    computeScores($metaVideos, $params);

    debug_log("############################################");
    debug_log("Original sorting:");
    foreach ($metaVideos as $metaVideo) {
        debug_log(" - score: " . $metaVideo->getScore() . "  ID: " . $metaVideo->getVideo()->getId());
    }

    if (!usort($metaVideos, 'compareMetaVideos')) {
        debug_log("COULD NOT SORT!");
    }

    debug_log("############################################");
    debug_log("Reranked videos:");
    $result = array();
    foreach ($metaVideos as $metaVideo) {
        array_push($result, $metaVideo->getVideo());
        debug_log(" - score: " . $metaVideo->getScore() . "  ID: " . $metaVideo->getVideo()->getId());
    }

    return $result;
}

/**
 * @param \MetaVideo[] $metaVideos
 * @param \RerankParams $params
 */
function computeScores($metaVideos, $params)
{
    foreach ($metaVideos as $metaVideo) {
        $metaVideo->invertDistances();
        $metaVideo->recalculateScore($params);
        debug_log("  ### video score: " . $metaVideo->getScore());
        debug_log("");
    }
}

/**
 * @param \MetaVideo $metaVideoA
 * @param \MetaVideo $metaVideoB
 * @return mixed
 */
function compareMetaVideos($metaVideoA, $metaVideoB)
{
    return sign($metaVideoB->getScore() - $metaVideoA->getScore());
}

/**
 * Get sign of a number.
 * @param $number
 * @return int -1, 0 or 1.
 */
function sign($number)
{
    return ($number > 0) ? 1 : (($number < 0) ? -1 : 0);
}


class DistanceMaxims
{
    public $maxOriginalPosition;
    public $maxDurationDistance;
    public $maxDatePublishedDistance;
    public $maxGpsDistance;
    public $maxViewsDistance;
    public $maxTudRatioDistance;
    public $maxAuthorNameDistance;

    /**
     * DistanceMaxims constructor.
     * @param $maxOriginalPosition
     * @param $maxDurationDistance
     * @param $maxDatePublishedDistance
     * @param $maxGpsDistance
     * @param $maxViewsDistance
     * @param $maxTudRatioDistance
     * @param $maxAuthorNameDistance
     */
    public function __construct($maxOriginalPosition,
                                $maxDurationDistance,
                                $maxDatePublishedDistance,
                                $maxGpsDistance,
                                $maxViewsDistance,
                                $maxTudRatioDistance,
                                $maxAuthorNameDistance)
    {
        $this->maxOriginalPosition = $maxOriginalPosition;
        $this->maxDurationDistance = $maxDurationDistance;
        $this->maxDatePublishedDistance = $maxDatePublishedDistance;
        $this->maxGpsDistance = $maxGpsDistance;
        $this->maxViewsDistance = $maxViewsDistance;
        $this->maxTudRatioDistance = $maxTudRatioDistance;
        $this->maxAuthorNameDistance = $maxAuthorNameDistance;
    }


}

class MetaVideo
{
    private $video;
    private $originalPosition;
    private $durationDistance;
    private $datePublishedDistance;
    private $gpsDistance;
    private $viewsDistance;
    private $tudRatioDistance;
    private $authorNameDistance;
    private $score;

    /**
     * MetaVideo constructor.
     * @param \Video $video
     */
    public function __construct($video)
    {
        $this->video = $video;
        $this->originalPosition = $video->getResultStanding();

        debug_log(" New MetaVideo, position: " . $this->originalPosition);
    }

    /**
     * Invert distance values. 0 becomes 1, 1 becomes 0.
     */
    public function invertDistances()
    {
        if ($this->originalPosition !== null) {
            $this->originalPosition = 1 - $this->originalPosition;
        }
        if ($this->durationDistance !== null) {
            $this->durationDistance = 1 - $this->durationDistance;
        }
        if ($this->datePublishedDistance !== null) {
            $this->datePublishedDistance = 1 - $this->datePublishedDistance;
        }
        if ($this->gpsDistance !== null) {
            $this->gpsDistance = 1 - $this->gpsDistance;
        }
        if ($this->viewsDistance !== null) {
            $this->viewsDistance = 1 - $this->viewsDistance;
        }
        if ($this->tudRatioDistance !== null) {
            $this->tudRatioDistance = 1 - $this->tudRatioDistance;
        }
        if ($this->authorNameDistance !== null) {
            $this->authorNameDistance = 1 - $this->authorNameDistance;
        }
    }

    /**
     * Refresh the $score attribute with given data.
     * @param \RerankParams $params
     */
    public function recalculateScore($params)
    {
        debug_log("  Recaulculating score for video: " . $this->video->getId());

        $this->score = 0;
        if ($params->getOriginalPositionWeight() === null) {
            debug_log("  Ignoring score from ResStanding, its weight is null.");
        } else if ($this->originalPosition === null) {
            debug_log("  Ignoring score from ResStanding, its calculated distance is null.");
        } else {
            $scoreInc = $this->originalPosition * $params->getOriginalPositionWeight();
            debug_log("  Adding score from ResStanding: " . $scoreInc);
            $this->score += $scoreInc;
        }

        if ($params->getDurationWeight() === null) {
            debug_log("  Ignoring score from Duration, its weight is null.");
        } else if ($this->durationDistance === null) {
            debug_log("  Ignoring score from Duration, its calculated distance is null.");
        } else {
            $scoreInc = $this->durationDistance * $params->getDurationWeight();
            debug_log("  Adding score from Duration: " . $scoreInc);
            $this->score += $scoreInc;
        }

        if ($params->getDatePublishedWeight() === null) {
            debug_log("  Ignoring score from DatePublished, its weight is null.");
        } else if ($this->datePublishedDistance === null) {
            debug_log("  Ignoring score from DatePublished, its calculated distance is null.");
        } else {
            $scoreInc = $this->datePublishedDistance * $params->getDatePublishedWeight();
            debug_log("  Adding score from DatePublished: " . $scoreInc);
            $this->score += $scoreInc;
        }

        if ($params->getGpsWeight() === null) {
            debug_log("  Ignoring score from Gps, its weight is null.");
        } else if ($this->gpsDistance === null) {
//            debug_log("  Ignoring score from Gps, its calculated distance is null.");
            debug_log(" GPS was not set for video. Replacing GPS with a const value.");
            $gps_missing_val = 0;
            $scoreInc = $gps_missing_val * $params->getGpsWeight();
            $this->score += $scoreInc;
        } else {
            $scoreInc = $this->gpsDistance * $params->getGpsWeight();
            debug_log("  Adding score from Gps: " . $scoreInc);
            debug_log("     gps " . $this->video->getLocation()["latitude"] . ", " . $this->video->getLocation()["longitude"]);
            $this->score += $scoreInc;
        }

        if ($params->getViewsWeight() === null) {
            debug_log("  Ignoring score from Views, its weight is null.");
        } else if ($this->viewsDistance === null) {
            debug_log("  Ignoring score from Views, its calculated distance is null.");
        } else {
            $scoreInc = $this->viewsDistance * $params->getViewsWeight();
            debug_log("  Adding score from Views: " . $scoreInc);
            $this->score += $scoreInc;
        }

        if ($params->getTudRatioWeight() === null) {
            debug_log("  Ignoring score from TudRatio, its weight is null.");
        } else if ($this->tudRatioDistance === null) {
            debug_log("  Ignoring score from TudRatio, its calculated distance is null.");
        } else {
            $scoreInc = $this->tudRatioDistance * $params->getTudRatioWeight();
            debug_log("  Adding score from TudRatio: " . $scoreInc);
            $this->score += $scoreInc;
        }

        if ($params->getAuthorNameWeight() === null) {
            debug_log("  Ignoring score from AuthorName, its weight is null.");
        } else if ($this->authorNameDistance === null) {
            debug_log("  Ignoring score from AuthorName, its calculated distance is null.");
        } else {
            $scoreInc = $this->authorNameDistance * $params->getAuthorNameWeight();
            debug_log("  Adding score from AuthorName: " . $scoreInc);
            $this->score += $scoreInc;
        }

    }


    /**
     * @return mixed
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param mixed $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return mixed
     */
    public function getOriginalPosition()
    {
        return $this->originalPosition;
    }

    /**
     * @param mixed $originalPosition
     */
    public function setOriginalPosition($originalPosition)
    {
        $this->originalPosition = $originalPosition;
    }


    /**
     * @return \Video
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * @param mixed $video
     */
    public function setVideo($video)
    {
        $this->video = $video;
    }

    /**
     * @return mixed
     */
    public function getDurationDistance()
    {
        return $this->durationDistance;
    }

    /**
     * @param mixed $durationDistance
     */
    public function setDurationDistance($durationDistance)
    {
        $this->durationDistance = $durationDistance;
    }

    /**
     * @return mixed
     */
    public function getDatePublishedDistance()
    {
        return $this->datePublishedDistance;
    }

    /**
     * @param mixed $datePublishedDistance
     */
    public function setDatePublishedDistance($datePublishedDistance)
    {
        $this->datePublishedDistance = $datePublishedDistance;
    }

    /**
     * @return mixed
     */
    public function getGpsDistance()
    {
        return $this->gpsDistance;
    }

    /**
     * @param mixed $gpsDistance
     */
    public function setGpsDistance($gpsDistance)
    {
        $this->gpsDistance = $gpsDistance;
    }

    /**
     * @return mixed
     */
    public function getViewsDistance()
    {
        return $this->viewsDistance;
    }

    /**
     * @param mixed $viewsDistance
     */
    public function setViewsDistance($viewsDistance)
    {
        $this->viewsDistance = $viewsDistance;
    }

    /**
     * @return mixed
     */
    public function getTudRatioDistance()
    {
        return $this->tudRatioDistance;
    }

    /**
     * @param mixed $tudRatioDistance
     */
    public function setTudRatioDistance($tudRatioDistance)
    {
        $this->tudRatioDistance = $tudRatioDistance;
    }

    /**
     * @return mixed
     */
    public function getAuthorNameDistance()
    {
        return $this->authorNameDistance;
    }

    /**
     * @param mixed $authorNameDistance
     */
    public function setAuthorNameDistance($authorNameDistance)
    {
        $this->authorNameDistance = $authorNameDistance;
    }


}

/**
 * Calculate score for a given video based on its duration.
 * @param \Video $video Video for which to calculate the score.
 * @param \RerankParams $params Settings for reranking.
 * @return double Calculated and weighted score in interval <0..1> where 0 is the worst and 1 the best possible score.
 */
function calcDurationDistance($video, $params)
{
    $txt = "duration";
    if (!attributeWanted(
        $params->getDurationRequested(),
        $params->getDurationWeight())
    ) {
        debug_log("Skipping calculating " . $txt . " distance. Not wanted.");
        return null;
    }

    // Calculating as per usual.
    debug_log("Calculating " . $txt . " distance.");
    return computeSimpleDistance($params->getDurationRequested(), $video->getLength());
}

/**
 * Calculate score for a given video.
 * @param \Video $video Video for which to calculate the score.
 * @param \RerankParams $params Settings for reranking.
 * @return int Distance.
 */
function calcDatePublishedDistance($video, $params)
{
    $txt = "date published";
    if (!attributeWanted(
        $params->getDatePublishedRequested(),
        $params->getDatePublishedWeight())
    ) {
        debug_log("Skipping calculating " . $txt . " distance. Not wanted.");
        return null;
    }

    // Calculating as per usual.
    debug_log("Calculating " . $txt . " distance.");
    return computeSimpleDistance($params->getDatePublishedRequested(), $video->getPublishedAt());
}

/**
 * Calculate score for a given video.
 * @param \Video $video Video for which to calculate the score.
 * @param \RerankParams $params Settings for reranking.
 * @return int Distance.
 */
function calcGpsDistance($video, $params)
{
    $txt = "gps";
    if (!attributeWanted(
        $params->getGpsRequested(),
        $params->getGpsWeight())
    ) {
        debug_log("Skipping calculating " . $txt . " distance. Not wanted.");
        return null;
    }

    $vidLatitude = $video->getLocation()["latitude"];
    $vidLongitude = $video->getLocation()["longitude"];

    if ($vidLatitude === null || $vidLongitude === null) {
        // GPS was not set
        debug_log("GPS was not set for the video.");
        return null;
    }

    $wantLatitude = $params->getGpsRequested()["latitude"];
    $wantLongitude = $params->getGpsRequested()["longitude"];

    $deltaLat = abs($vidLatitude - $wantLatitude);
    $deltaLon = abs($vidLongitude - $wantLongitude);

    $centralAngle = 2 * acos(sin($vidLatitude) * sin($wantLatitude) + cos($vidLatitude) * cos($wantLatitude) * cos($deltaLon));

    $r = 6371.0088; // radius of Earth in kilometers
    $distance = $r * $centralAngle;

    return $distance;
}

/**
 * Calculate score for a given video.
 * @param \Video $video Video for which to calculate the score.
 * @param \RerankParams $params Settings for reranking.
 * @return int Distance.
 */
function calcViewsDistance($video, $params)
{
    $txt = "views";
    if (!attributeWanted(
        $params->getViewsRequested(),
        $params->getViewsWeight())
    ) {
        debug_log("Skipping calculating " . $txt . " distance. Not wanted.");
        return null;
    }

    // Calculating as per usual.
    debug_log("Calculating " . $txt . " distance.");
    return computeSimpleDistance($params->getViewsRequested(), $video->getViewCount());
}

/**
 * Calculate score for a given video.
 * @param \Video $video Video for which to calculate the score.
 * @param \RerankParams $params Settings for reranking.
 * @return int Distance.
 */
function calcTudRatioDistance($video, $params)
{
    return null;
}

/**
 * Calculate score for a given video.
 * @param \Video $video Video for which to calculate the score.
 * @param \RerankParams $params Settings for reranking.
 * @return int Distance.
 */
function calcAuthorNameDistance($video, $params)
{
    return null;
}

/**
 * Find if given attribute is wanted / needed to calculate final score.
 * @param int|float $requestedVal Value requested by the user.
 * @param float $weight Weight of the given value.
 * @return bool
 */
function attributeWanted($requestedVal, $weight)
{
    if ($requestedVal === null ||
        $weight === null ||
        $weight <= 0.0
    ) {
        return false;
    } else {
        return true;
    }
}

function computeSimpleDistance($requested, $actual)
{
    debug_log("  Requested: " . $requested);
    debug_log("  Actual:    " . $actual);
    $distance = abs($actual - $requested);
    debug_log("    distance: " . $distance);

    return $distance;
}