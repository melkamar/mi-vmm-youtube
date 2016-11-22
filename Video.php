<?php

class Video {

    /**
     *
     * @var integer On which position was this result returned
     */
    private $resultStanding;
    
    /**
     *
     * @var id string YouTube video id  
     */
    private $id;
    
    /**
     *
     * @var string Title of the video
     */
    private $title;
    
    /**
     *
     * @var string Description of the video
     */
    private $description;
    /**
     *
     * @var integer Number of views
     */
    private $viewCount;
    /**
     *
     * @var integer Number of likes
     */
    private $likeCount;
    
    /**
     *
     * @var integer Number of dislikes
     */
    private $dislikeCount;

    /**
     * Field "default" is always present.
     * default - 120x90 
     * medium - 320x180
     * high - 480x360
     * standard - 640x480
     * maxres - 1280x720
     * @var Array Array of URLs to different resolutions of thumbnail.
     */
    private $thumbnails;

    /**
     * Unix time stamp when the video was published
     * @var integer Publish time in Unix time stamp format
     */
    private $publishedAt;
    /**
     * "latitude", "longitude" and "altitude fields. Altitude is usually 0.
     *  Null when information wasn't present at the time of fetching.
     * @var Array|NULL Representing GPS location. Fields: "latitude", "longitude" and "altitude". Or NULL. 
     */
    private $location;
    
    /**
     * Duration of video in seconds
     * @var integer Duration in seconds
     */
    private $length;
    
    /**
     * Name of channel on which is video uploaded
     * @var string Name of channel on which is video uploaded
     */
    private $author;
    
    /**
     * YouTube ID string identifying the channel on which is video uploaded
     * @var string YouTube ID of channel
     */
    private $channelId;

    function __construct() {
        
    }

    /**
     * As PHP may reorder the collection
     * @return integer Position on which was this video returned
     */
    function getResultStanding() {
        return $this->resultStanding;
    }
        
    /**
     * 
     * @return integer Number of dislikes on video
     */
    function getDislikeCount() {
        return $this->dislikeCount;
    }

    /**
     * 
     * @return string YouTube ID string of the video
     */
    function getId() {
        return $this->id;
    }

    /**
     * 
     * @return string Title of the video
     */
    function getTitle() {
        return $this->title;
    }

    /**
     * FUll length description of video.
     * @return string Description field of the video
     */
    function getDescription() {
        return $this->description;
    }

    /**
     * 
     * @return integer Number of views
     */
    function getViewCount() {
        return $this->viewCount;
    }

    /**
     * Number of likes on video
     * @return integer Number of likes on video
     */
    function getLikeCount() {
        return $this->likeCount;
    }

    /**
     * Field "default" is always present.
     * default - 120x90 
     * medium - 320x180
     * high - 480x360
     * standard - 640x480
     * maxres - 1280x720
     * @return array Array of thumbnail URLs in different resolutions.
     */
    function getThumbnails() {
        return $this->thumbnails;
    }

    /**
     * Example of return: 1470503878
     * @return integer Unix time stamp of time when the video was published
     */
    function getPublishedAt() {
        return $this->publishedAt;
    }

    /**
     * Returned object has "latitude", "longitude" and "altitude fields. Altitude is usually 0.
     * @return Array|NULL Array representing location structure or null when information wasn't present.
     */
    function getLocation() {
        return $this->location;
    }

    /**
     * 
     * @return integer Duration of video in seconds
     */
    function getLength() {
        return $this->length;
    }

    /**
     * 
     * @return string Name of the channel on which is video uploaded
     */
    function getAuthor() {
        return $this->author;
    }

    /**
     * 
     * @return string YouTube ID of channel on which is video uploaded
     */
    function getChannelId() {
        return $this->channelId;
    }
    
    /**
     * 
     * @param integer $resultStanding Position on which was this video returned
     */
    function setResultStanding($resultStanding) {
        $this->resultStanding = $resultStanding;
    }

    
    /**
     * Sets video ID. Usefull when creating, shouldn't be used in re-ranking phase
     * @param string $id YouTube id of the video
     */
    function setId($id) {
        $this->id = $id;
    }

    /**
     * Sets video title. Should be used only in fetching phase
     * @param string $title Title of video
     */
    function setTitle($title) {
        $this->title = $title;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setViewCount($viewCount) {
        $this->viewCount = $viewCount;
    }

    function setLikeCount($likeCount) {
        $this->likeCount = $likeCount;
    }

    function setDislikeCount($dislikeCount) {
        $this->dislikeCount = $dislikeCount;
    }

    function setThumbnails($thumbnails) {
        $this->thumbnails = $thumbnails;
    }

    function setPublishedAt($publishedAt) {
        $this->publishedAt = $publishedAt;
    }

    function setLocation($location) {
        $this->location = $location;
    }

    function setLength($length) {
        $this->length = $length;
    }

    function setAuthor($author) {
        $this->author = $author;
    }

    function setChannelId($channelId) {
        $this->channelId = $channelId;
    }

}
