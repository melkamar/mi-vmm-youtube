<?php
/**
 * Created by IntelliJ IDEA.
 * User: Martin
 * Date: 06.12.2016
 * Time: 21:24
 */

/**
 * Normalize distances of all given videos into numbers between 0 and 1.
 * @param \MetaVideo[] $metaVideos Array of \MetaVideo objects whose attributes to normalize.
 * @param \DistanceMaxims $maxims Maximum distances found.
 */
function normalize($metaVideos, $maxims)
{
    foreach ($metaVideos as $metaVideo) {
        debug_log("  (normalizing) original values:");
        debug_log("    originalPosition: " . $metaVideo->getOriginalPosition());
        debug_log("    durationDistance: " . $metaVideo->getDurationDistance());
        debug_log("    datePublishedDistance: " . $metaVideo->getDatePublishedDistance());
        debug_log("    gpsDistance: " . $metaVideo->getGpsDistance());
        debug_log("    viewsDistance: " . $metaVideo->getViewsDistance());
        debug_log("    tudRatioDistance: " . $metaVideo->getTudRatioDistance());
        debug_log("    authorNameDistance: " . $metaVideo->getAuthorNameDistance());

        $metaVideo->setOriginalPosition(normalizeValue($metaVideo->getOriginalPosition(), $maxims->maxOriginalPosition));
        $metaVideo->setDurationDistance(normalizeValue($metaVideo->getDurationDistance(), $maxims->maxDurationDistance));
        $metaVideo->setDatePublishedDistance(normalizeValue($metaVideo->getDatePublishedDistance(), $maxims->maxDatePublishedDistance));
        $metaVideo->setGpsDistance(normalizeValue($metaVideo->getGpsDistance(), $maxims->maxGpsDistance));
        $metaVideo->setViewsDistance(normalizeValue($metaVideo->getViewsDistance(), $maxims->maxViewsDistance));
        $metaVideo->setTudRatioDistance(normalizeValue($metaVideo->getTudRatioDistance(), $maxims->maxTudRatioDistance));
        $metaVideo->setAuthorNameDistance(normalizeValue($metaVideo->getAuthorNameDistance(), $maxims->maxAuthorNameDistance));

        debug_log("  normalized values:");
        debug_log("    originalPosition: " . $metaVideo->getOriginalPosition());
        debug_log("    durationDistance: " . $metaVideo->getDurationDistance());
        debug_log("    datePublishedDistance: " . $metaVideo->getDatePublishedDistance());
        debug_log("    gpsDistance: " . $metaVideo->getGpsDistance());
        debug_log("    viewsDistance: " . $metaVideo->getViewsDistance());
        debug_log("    tudRatioDistance: " . $metaVideo->getTudRatioDistance());
        debug_log("    authorNameDistance: " . $metaVideo->getAuthorNameDistance());

        debug_log("");
    }
}

/**
 * Normalizes a value in respect to a maximum.
 * @param $value
 * @param $maximum
 * @return float|int|null Normalized value if ok, null if either parameter is null.
 */
function normalizeValue($value, $maximum)
{
    if ($value === null) {
        debug_log("  not normalizing value, it is null.");
        return null;
    } else if ($maximum === null) {
        debug_log("  value is set, but maximum is null!");
        return null;
    } else {
        return $value / $maximum;
    }
}