<?php
/**
 * Created by IntelliJ IDEA.
 * User: Martin
 * Date: 30.11.2016
 * Time: 20:25
 */

include_once 'core.php';
include_once 'util.php';

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
    $rerankings = array();
    $maxDurationDistance = null;
    $maxDatePublishedDistance = null;
    $maxGpsDistance = null;
    $maxViewsDistance = null;
    $maxTudRatioDistance = null;
    $maxAuthorNameDistance = null;

    foreach ($resultCollection as $video) {
        debug_log(">>> Processing video " . $video->getId());

        $reranking = new VideoReranking();
        $reranking->setDurationDistance(calcDurationDistance($video, $params));
        $reranking->setDatePublishedDistance(calcDatePublishedDistance($video, $params));
        $reranking->setGpsDistance(calcGpsDistance($video, $params));
        $reranking->setViewsDistance(calcViewsDistance($video, $params));
        $reranking->setTudRatioDistance(calcTudRatioDistance($video, $params));
        $reranking->setAuthorNameDistance(calcAuthorNameDistance($video, $params));

        if ($maxDurationDistance === null) {
            $maxDurationDistance = $reranking->getDurationDistance();
        } else {
            if ($reranking->getDurationDistance() !== null && $reranking->getDurationDistance() > $maxDurationDistance)
                $maxDurationDistance = $reranking->getDurationDistance();
        }

        if ($maxDatePublishedDistance === null) {
            $maxDatePublishedDistance = $reranking->getDatePublishedDistance();
        } else {
            if ($reranking->getDatePublishedDistance() !== null && $reranking->getDatePublishedDistance() > $maxDatePublishedDistance)
                $maxDatePublishedDistance = $reranking->getDatePublishedDistance();
        }

        if ($maxGpsDistance === null) {
            $maxGpsDistance = $reranking->getGpsDistance();
        } else {
            if ($reranking->getGpsDistance() !== null && $reranking->getGpsDistance() > $maxGpsDistance)
                $maxGpsDistance = $reranking->getGpsDistance();
        }

        if ($maxViewsDistance === null) {
            $maxViewsDistance = $reranking->getViewsDistance();
        } else {
            if ($reranking->getViewsDistance() !== null && $reranking->getViewsDistance() > $maxViewsDistance)
                $maxViewsDistance = $reranking->getViewsDistance();
        }

        if ($maxTudRatioDistance === null) {
            $maxTudRatioDistance = $reranking->getTudRatioDistance();
        } else {
            if ($reranking->getTudRatioDistance() !== null && $reranking->getTudRatioDistance() > $maxTudRatioDistance)
                $maxTudRatioDistance = $reranking->getTudRatioDistance();
        }

        if ($maxAuthorNameDistance === null) {
            $maxAuthorNameDistance = $reranking->getAuthorNameDistance();
        } else {
            if ($reranking->getAuthorNameDistance() !== null && $reranking->getAuthorNameDistance() > $maxAuthorNameDistance)
                $maxAuthorNameDistance = $reranking->getAuthorNameDistance();
        }


        array_push($rerankings, $reranking);

        debug_log("");
        debug_log("");
    }

    // Normalize using the obtained maxims
    debug_log("");
    debug_log(">>> Normalizing...");

}

class VideoReranking
{
    private $video;
    private $durationDistance;
    private $datePublishedDistance;
    private $gpsDistance;
    private $viewsDistance;
    private $tudRatioDistance;
    private $authorNameDistance;

    /**
     * @return mixed
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
    if ($params->getDurationRequested() === null ||
        $params->getDurationWeight() === null ||
        $params->getDurationWeight() <= 0.0
    ) {
        debug_log("Skipping calculating duration distance. Not wanted.");
        return null;
    }

    // Calculating as per usual.
    debug_log("Calculating " . $txt . " distance.");
    debug_log("  Requested " . $txt . ": " . $params->getDurationRequested());
    debug_log("  Video " . $txt . ":     " . $video->getLength());
    $distance = abs($video->getLength() - $params->getDurationRequested());
    debug_log("    distance: " . $distance);

    return $distance;
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
    if ($params->getDatePublishedRequested() === null ||
        $params->getDatePublishedWeight() === null ||
        $params->getDatePublishedWeight() <= 0.0
    ) {
        debug_log("Skipping calculating " . $txt . " distance. Not wanted.");
        return null;
    }

    // Calculating as per usual.
    debug_log("Calculating " . $txt . " distance.");
    debug_log("  Requested " . $txt . ": " . $params->getDatePublishedRequested());
    debug_log("  Video " . $txt . ":     " . $video->getPublishedAt());
    $distance = abs($video->getPublishedAt() - $params->getDatePublishedRequested());
    debug_log("    distance: " . $distance);

    return $distance;
}

/**
 * Calculate score for a given video.
 * @param \Video $video Video for which to calculate the score.
 * @param \RerankParams $params Settings for reranking.
 * @return int Distance.
 */
function calcGpsDistance($video, $params)
{
    return null;
}

/**
 * Calculate score for a given video.
 * @param \Video $video Video for which to calculate the score.
 * @param \RerankParams $params Settings for reranking.
 * @return int Distance.
 */
function calcViewsDistance($video, $params)
{
    return null;
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