<?php

/**
 * Created by IntelliJ IDEA.
 * User: Martin
 * Date: 07.12.2016
 * Time: 2:00
 */
class RerankedVideo
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
            debug_log("    " . $params->getAuthorNameRequested() . " --> " . $this->video->getAuthor());
            $this->score += $scoreInc;
        }

    }

    /**
     * @param \RerankedVideo $metaVideoA
     * @param \RerankedVideo $metaVideoB
     * @return mixed
     */
    static function compareMetaVideos($metaVideoA, $metaVideoB)
    {
        return sign($metaVideoB->getScore() - $metaVideoA->getScore());
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