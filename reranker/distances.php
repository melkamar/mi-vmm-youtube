<?php
/**
 * Created by IntelliJ IDEA.
 * User: Martin
 * Date: 07.12.2016
 * Time: 2:18
 */

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