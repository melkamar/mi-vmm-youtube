<?php

/**
 * Created by IntelliJ IDEA.
 * User: Martin
 * Date: 06.12.2016
 * Time: 18:01
 */
class RerankParams
{
    /**
     * @var double|null Weight of video's original standing in the results. Must be in interval <0..1>.
     */
    private $resStandingWeight;

    /**
     * @var double Weight of video's duration. Must be in interval <0..1>.
     */
    private $durationWeight;

    /**
     * @var double Weight of video's published date. Must be in interval <0..1>.
     */
    private $datePublishedWeight;

    /**
     * @var double Weight of video's gps location. Must be in interval <0..1>.
     */
    private $gpsWeight;

    /**
     * @var double Weight of video's view count. Must be in interval <0..1>.
     */
    private $viewsWeight;

    /**
     * @var double Weight of video's thumbs up/down ratio. Must be in interval <0..1>.
     */
    private $tudRatioWeight;

    /**
     * @var double Weight of video's author name. Must be in interval <0..1>.
     */
    private $authorNameWeight;

    /**
     * @var int Requsted duration of the video in seconds.
     */
    private $durationRequested;

    /**
     * @var integer Requested published time as a Unix timestamp.
     */
    private $datePublishedRequested;

    /**
     * @var array Requested GPS location for the video. Fields: "latitude", "longitude", "altitude".
     */
    private $gpsRequested;

    /**
     * @var int Requested number of views of the video.
     */
    private $viewsRequested;

    /**
     * @var double Requested ratio of thumbs up/down.
     */
    private $tudRatioRequested;

    /**
     * @var string Requested author name.
     */
    private $authorNameRequested;

    /**
     * RerankParams constructor.
     */
    public function __construct()
    {
        $this->resStandingWeight = 0.1;
        $this->durationWeight = null;
        $this->datePublishedWeight = null;
        $this->gpsWeight = null;
        $this->viewsWeight = null;
        $this->tudRatioWeight = null;
        $this->authorNameWeight = null;
        $this->durationRequested = null;
        $this->datePublishedRequested = null;
        $this->gpsRequested = null;
        $this->viewsRequested = null;
        $this->tudRatioRequested = null;
        $this->authorNameRequested = null;
    }




    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @return float
     */
    public function getResStandingWeight()
    {
        return $this->resStandingWeight;
    }

    public function setResStandingWeight($resStandingWeight)
    {
        $this->resStandingWeight = $resStandingWeight;
    }

    /**
     * @return float
     */
    public function getDurationWeight()
    {
        return $this->durationWeight;
    }

    /**
     * @param float $durationWeight
     */
    public function setDurationWeight($durationWeight)
    {
        $this->durationWeight = $durationWeight;
    }

    /**
     * @return float
     */
    public function getDatePublishedWeight()
    {
        return $this->datePublishedWeight;
    }

    /**
     * @param float $datePublishedWeight
     */
    public function setDatePublishedWeight($datePublishedWeight)
    {
        $this->datePublishedWeight = $datePublishedWeight;
    }

    /**
     * @return float
     */
    public function getGpsWeight()
    {
        return $this->gpsWeight;
    }

    /**
     * @param float $gpsWeight
     */
    public function setGpsWeight($gpsWeight)
    {
        $this->gpsWeight = $gpsWeight;
    }

    /**
     * @return float
     */
    public function getViewsWeight()
    {
        return $this->viewsWeight;
    }

    /**
     * @param float $viewsWeight
     */
    public function setViewsWeight($viewsWeight)
    {
        $this->viewsWeight = $viewsWeight;
    }

    /**
     * @return float
     */
    public function getTudRatioWeight()
    {
        return $this->tudRatioWeight;
    }

    /**
     * @param float $tudRatioWeight
     */
    public function setTudRatioWeight($tudRatioWeight)
    {
        $this->tudRatioWeight = $tudRatioWeight;
    }

    /**
     * @return float
     */
    public function getAuthorNameWeight()
    {
        return $this->authorNameWeight;
    }

    /**
     * @param float $authorNameWeight
     */
    public function setAuthorNameWeight($authorNameWeight)
    {
        $this->authorNameWeight = $authorNameWeight;
    }

    /**
     * @return int
     */
    public function getDurationRequested()
    {
        return $this->durationRequested;
    }

    /**
     * @param int $durationRequested
     */
    public function setDurationRequested($durationRequested)
    {
        $this->durationRequested = $durationRequested;
    }

    /**
     * @return int
     */
    public function getDatePublishedRequested()
    {
        return $this->datePublishedRequested;
    }

    /**
     * @param int $datePublishedRequested
     */
    public function setDatePublishedRequested($datePublishedRequested)
    {
        $this->datePublishedRequested = $datePublishedRequested;
    }

    /**
     * @return array
     */
    public function getGpsRequested()
    {
        return $this->gpsRequested;
    }

    /**
     * @param array $gpsRequested
     */
    public function setGpsRequested($gpsRequested)
    {
        $this->gpsRequested = $gpsRequested;
    }

    /**
     * @return int
     */
    public function getViewsRequested()
    {
        return $this->viewsRequested;
    }

    /**
     * @param int $viewsRequested
     */
    public function setViewsRequested($viewsRequested)
    {
        $this->viewsRequested = $viewsRequested;
    }

    /**
     * @return float
     */
    public function getTudRatioRequested()
    {
        return $this->tudRatioRequested;
    }

    /**
     * @param float $tudRatioRequested
     */
    public function setTudRatioRequested($tudRatioRequested)
    {
        $this->tudRatioRequested = $tudRatioRequested;
    }

    /**
     * @return string
     */
    public function getAuthorNameRequested()
    {
        return $this->authorNameRequested;
    }

    /**
     * @param string $authorNameRequested
     */
    public function setAuthorNameRequested($authorNameRequested)
    {
        $this->authorNameRequested = $authorNameRequested;
    }


}