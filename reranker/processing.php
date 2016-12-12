<?php
/**
 * Created by IntelliJ IDEA.
 * User: Martin
 * Date: 30.11.2016
 * Time: 20:25
 */

include_once 'formatting.php';
include_once 'fetching.php';
include_once 'util.php';
include_once 'normalizing.php';
include_once 'classes/RerankedVideo.php';
include_once 'distances.php';

/**
 * Main method of reranking engine.
 *
 * This is the only method you want to call to get the reranking to work.
 *
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

        $maxOriginalPosition = getMaxArg($maxOriginalPosition, $rerankedVideo->getOriginalPosition());
        $maxDurationDistance = getMaxArg($maxDurationDistance, $rerankedVideo->getDurationDistance());
        $maxDatePublishedDistance = getMaxArg($maxDatePublishedDistance, $rerankedVideo->getDatePublishedDistance());
        $maxGpsDistance = getMaxArg($maxGpsDistance, $rerankedVideo->getGpsDistance());
        $maxViewsDistance = getMaxArg($maxViewsDistance, $rerankedVideo->getViewsDistance());
        $maxTudRatioDistance = getMaxArg($maxTudRatioDistance, $rerankedVideo->getTudRatioDistance());
        $maxAuthorNameDistance = getMaxArg($maxAuthorNameDistance, $rerankedVideo->getAuthorNameDistance());


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

    // Mostly debug logging
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
 * Get the higher of the two values, respecting null.
 * @param $currentMax
 * @param $newVal
 * @return mixed
 */
function getMaxArg($currentMax, $newVal)
{
    if ($currentMax === null) {
        return $newVal;
    } else {
        if ($newVal !== null &&
            $newVal > $currentMax
        ) {
            return $newVal;
        } else {
            return $currentMax;
        }
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



