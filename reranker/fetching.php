<?php

include_once 'classes/Video.php';
include_once './util.php';

/**
 * 
 * @param string $durationString Time interval in ISO8061 format
 * @return integer Duration of given interval in seconds
 */
function durationToSeconds($durationString) {
    $interval = new DateInterval($durationString);
    return ($interval->y * 365 * 24 * 60 * 60) +
            ($interval->m * 30 * 24 * 60 * 60) +
            ($interval->d * 24 * 60 * 60) +
            ($interval->h * 60 * 60) +
            ($interval->i * 60) +
            $interval->s;
}

/**
 * Fetches first x result of search of given term and parses the JSON result into collection of \Video objects which is then returned 
 * @param integer $searchQuery Term that should be inserted into search
 * @return \Video[]
 */
function fetchSearchResult($searchQuery, $videoLimit) {
    // YouTube API key
    $token = "AIzaSyClwW3tIsqKxmFWP4l6YpEP78oCfJ9TzsM";
    $outputCollection = array();
    $videosAddedCount = 0;
    $nextPageToken = "";
    $resultsPerPage = 50;

    // initialize curl
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    while ($videosAddedCount < $videoLimit) {
        // parameters for search query. Must be URL encoded.
        $query = http_build_query([
            'key' => $token,
            'part' => 'snippet',
            'type' => 'video',
            'fields' => 'items(id),nextPageToken,pageInfo(totalResults)',
            'maxResults' => $resultsPerPage,
            'pageToken' => $nextPageToken,
            'q' => $searchQuery
        ]);

        // create URL for search request and put it into curl
        $searchUrl = "https://www.googleapis.com/youtube/v3/search?" . $query;
        curl_setopt($ch, CURLOPT_URL, $searchUrl);

        //execute curl request, decode response from JSON
        $result = curl_exec($ch);
        $json = json_decode($result, true);

        debug_log("GET request to " . $searchUrl . " \n");


        //save all fetched IDs and make comma-separated string out of them
        $ids = array();
        $idStringTmp = "";
        foreach ($json["items"] as $item) {
            array_push($ids, $item["id"]["videoId"]);
            $idStringTmp = $idStringTmp . "," . $item["id"]["videoId"];
        }
        //remove first comma
        $idString = substr($idStringTmp, 1);

        debug_log(sizeof($ids) . " video ids fetched.\n");
        debug_log("idString = \"" . $idString . "\"\n");

        // parameters for video details query
        $videoQuery = http_build_query([
            'key' => $token,
            'part' => 'contentDetails,snippet,statistics,recordingDetails',
            'fields' => 'items(id,snippet(publishedAt,channelId,title,description,thumbnails,channelTitle),contentDetails(duration,definition),statistics(viewCount,likeCount,dislikeCount),recordingDetails(location))',
            'maxResults' => '50',
            'id' => $idString
        ]);

        //create URL for video details request
        $videoUrl = "https://www.googleapis.com/youtube/v3/videos?" . $videoQuery;

        //execute curl request, decode JSON response
        curl_setopt($ch, CURLOPT_URL, $videoUrl);
        $resultVideo = curl_exec($ch);
        $jsonVideo = json_decode($resultVideo, true);


        debug_log("GET request to " . $videoUrl . " \n");


        // for each video, make new \Video object and fill in parameters parsed from JSON
        foreach ($jsonVideo["items"] as $item) {
            $videosAddedCount++;
            $video = new Video();
            // always present
            $video->setResultStanding($videosAddedCount);
            $video->setId($item["id"]);
            $video->setTitle($item["snippet"]["title"]);
            $video->setDescription($item["snippet"]["description"]);
            $video->setViewCount($item["statistics"]["viewCount"]);
            $video->setThumbnails($item["snippet"]["thumbnails"]);
            $video->setAuthor($item["snippet"]["channelTitle"]);
            $video->setChannelId($item["snippet"]["channelId"]);

            // might not be present
            if (!isset($item["statistics"]["likeCount"])) {
                $video->setLikeCount(null);
                $video->setDislikeCount(null);
            } else {
                $video->setLikeCount($item["statistics"]["likeCount"]);
                $video->setDislikeCount($item["statistics"]["dislikeCount"]);
            }

            if (isset($item["recordingDetails"]["location"])) {
                $video->setLocation($item["recordingDetails"]["location"]);
            } else {
                $video->setLocation(NULL);
            }

            // needs processing
            $video->setPublishedAt(strtotime($item["snippet"]["publishedAt"]));
            $video->setLength(durationToSeconds($item["contentDetails"]["duration"]));

            // add object to the collection
            array_push($outputCollection, $video);
        }


        debug_log("Added " . $videosAddedCount . " videos to the collection\n");


        if (isset($json["nextPageToken"])) {
            $nextPageToken = $json["nextPageToken"];
        } else {
            break;
        }
    }

    //return collection
    return $outputCollection;
}
