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
include_once 'RerankedVideo.php';

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
    /** @var \RerankedVideo[] $rerankedVideos */
    $rerankedVideos = array();
    $maxOriginalPosition = null;
    $maxDurationDistance = null;
    $maxDatePublishedDistance = null;
    $maxGpsDistance = null;
    $maxViewsDistance = null;
    $maxTudRatioDistance = null;
    $maxAuthorNameDistance = null;

    foreach ($resultCollection as $video) {
        debug_log(">>> Processing video " . $video->getId());

        $rerankedVideo = new RerankedVideo($video);
        $rerankedVideo->setDurationDistance(calcDurationDistance($video, $params));
        $rerankedVideo->setDatePublishedDistance(calcDatePublishedDistance($video, $params));
        $rerankedVideo->setGpsDistance(calcGpsDistance($video, $params));
        $rerankedVideo->setViewsDistance(calcViewsDistance($video, $params));
        $rerankedVideo->setTudRatioDistance(calcTudRatioDistance($video, $params));
        $rerankedVideo->setAuthorNameDistance(calcAuthorNameDistance($video, $params));

        if ($maxOriginalPosition === null) {
            $maxOriginalPosition = $rerankedVideo->getOriginalPosition();
        } else {
            if ($rerankedVideo->getOriginalPosition() !== null && $rerankedVideo->getOriginalPosition() > $maxOriginalPosition)
                $maxOriginalPosition = $rerankedVideo->getOriginalPosition();
        }

        if ($maxDurationDistance === null) {
            $maxDurationDistance = $rerankedVideo->getDurationDistance();
        } else {
            if ($rerankedVideo->getDurationDistance() !== null && $rerankedVideo->getDurationDistance() > $maxDurationDistance)
                $maxDurationDistance = $rerankedVideo->getDurationDistance();
        }

        if ($maxDatePublishedDistance === null) {
            $maxDatePublishedDistance = $rerankedVideo->getDatePublishedDistance();
        } else {
            if ($rerankedVideo->getDatePublishedDistance() !== null && $rerankedVideo->getDatePublishedDistance() > $maxDatePublishedDistance)
                $maxDatePublishedDistance = $rerankedVideo->getDatePublishedDistance();
        }

        if ($maxGpsDistance === null) {
            $maxGpsDistance = $rerankedVideo->getGpsDistance();
        } else {
            if ($rerankedVideo->getGpsDistance() !== null && $rerankedVideo->getGpsDistance() > $maxGpsDistance)
                $maxGpsDistance = $rerankedVideo->getGpsDistance();
        }

        if ($maxViewsDistance === null) {
            $maxViewsDistance = $rerankedVideo->getViewsDistance();
        } else {
            if ($rerankedVideo->getViewsDistance() !== null && $rerankedVideo->getViewsDistance() > $maxViewsDistance)
                $maxViewsDistance = $rerankedVideo->getViewsDistance();
        }

        if ($maxTudRatioDistance === null) {
            $maxTudRatioDistance = $rerankedVideo->getTudRatioDistance();
        } else {
            if ($rerankedVideo->getTudRatioDistance() !== null && $rerankedVideo->getTudRatioDistance() > $maxTudRatioDistance)
                $maxTudRatioDistance = $rerankedVideo->getTudRatioDistance();
        }

        if ($maxAuthorNameDistance === null) {
            $maxAuthorNameDistance = $rerankedVideo->getAuthorNameDistance();
        } else {
            if ($rerankedVideo->getAuthorNameDistance() !== null && $rerankedVideo->getAuthorNameDistance() > $maxAuthorNameDistance)
                $maxAuthorNameDistance = $rerankedVideo->getAuthorNameDistance();
        }


        array_push($rerankedVideos, $rerankedVideo);

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

    normalize($rerankedVideos, $maxims);
    computeScores($rerankedVideos, $params);

    debug_log("############################################");
    debug_log("Original sorting:");
    foreach ($rerankedVideos as $rerankedVideo) {
        debug_log(" - score: " . $rerankedVideo->getScore() . "  ID: " . $rerankedVideo->getVideo()->getId());
    }

    if (!usort($rerankedVideos, array('RerankedVideo', 'compareMetaVideos'))) {
        debug_log("COULD NOT SORT!");
    }

    debug_log("############################################");
    debug_log("Reranked videos:");
    $result = array();
    foreach ($rerankedVideos as $rerankedVideo) {
        array_push($result, $rerankedVideo->getVideo());
        debug_log(" - score: " . $rerankedVideo->getScore() . "  ID: " . $rerankedVideo->getVideo()->getId());
    }

    return $result;
}

/**
 * @param \RerankedVideo[] $metaVideos
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
    $txt = "tud ratio";
    if (!attributeWanted(
        $params->getTudRatioRequested(),
        $params->getTudRatioWeight())
    ) {
        debug_log("Skipping calculating " . $txt . " distance. Not wanted.");
        return null;
    } else if (
        $video->getLikeCount() === null ||
        $video->getDislikeCount() === null ||
        $video->getLikeCount() + $video->getDislikeCount() == 0
    ) {
        debug_log("Skipping calculating " . $txt . " distance. Likes/dislikes not present or zero.");
        return null;
    }

    // Calculating as per usual.
    debug_log("Calculating " . $txt . " distance.");
    $videoRatio = $video->getLikeCount() / ($video->getLikeCount() + $video->getDislikeCount());
    return computeSimpleDistance($params->getTudRatioRequested(), $videoRatio);
}

/**
 * Calculate score for a given video.
 * @param \Video $video Video for which to calculate the score.
 * @param \RerankParams $params Settings for reranking.
 * @return int Distance.
 */
function calcAuthorNameDistance($video, $params)
{
    $txt = "author name";
    if (!attributeWanted(
        $params->getAuthorNameRequested(),
        $params->getAuthorNameWeight())
    ) {
        debug_log("Skipping calculating " . $txt . " distance. Not wanted.");
        return null;
    }

    // Calculating as per usual.
    debug_log("Calculating " . $txt . " distance.");
    if ($params->isAuthorNameCaseSensitive()) {
        return levenshtein($params->getAuthorNameRequested(), $video->getAuthor());
    } else {
        return levenshtein(strtolower($params->getAuthorNameRequested()), strtolower($video->getAuthor()));
    }

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