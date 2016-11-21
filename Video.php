<?php

class Video {

    private $id;
    private $title;
    private $description;
    private $viewCount;
    private $likeCount;
    private $thumbnails;
    private $publishedAt;
    private $location;
    private $length;
    private $author;
    private $channelId;
    
    
    function __construct() {
    }
    function getId() {
        return $this->id;
    }

    function getTitle() {
        return $this->title;
    }

    function getDescription() {
        return $this->description;
    }

    function getViewCount() {
        return $this->viewCount;
    }

    function getLikeCount() {
        return $this->likeCount;
    }

    function getThumbnails() {
        return $this->thumbnails;
    }

    function getPublishedAt() {
        return $this->publishedAt;
    }

    function getLocation() {
        return $this->location;
    }

    function getLength() {
        return $this->length;
    }

    function getAuthor() {
        return $this->author;
    }

    function getChannelId() {
        return $this->channelId;
    }

    function setId($id) {
        $this->id = $id;
    }

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
