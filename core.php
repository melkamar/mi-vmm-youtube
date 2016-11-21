<?php

include_once './Video.php';

function fetchSearchResult($searchQuery) {
    $token = "AIzaSyClwW3tIsqKxmFWP4l6YpEP78oCfJ9TzsM";
    $debug = true;
    $outputCollection = array();

    $query = http_build_query([
        'key' => $token,
        'part' => 'snippet',
        'type' => 'video',
        'fields' => 'items(id)',
        'maxResults' => '50',
        'q' => $searchQuery
    ]);

    $searchUrl = "https://www.googleapis.com/youtube/v3/search?" . $query;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $searchUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $json = json_decode($result, true);
    if ($debug) {
        echo "GET request to " . $searchUrl . " \n";
    }
    $ids = array();
    $idStringTmp = "";
    foreach ($json["items"] as $item) {
        array_push($ids, $item["id"]["videoId"]);
        $idStringTmp = $idStringTmp . "," . $item["id"]["videoId"];
    }
    $idString = substr($idStringTmp, 1);

    if ($debug) {
        echo sizeof($ids) . " video ids fetched.\n";
        echo "idString = \"" . $idString . "\"\n";
    }

    $videoQuery = http_build_query([
        'key' => $token,
        'part' => 'contentDetails,snippet,statistics,recordingDetails',
        'fields' => 'items(id,snippet(publishedAt,channelId,title,description,thumbnails,channelTitle),contentDetails(duration,definition),statistics(viewCount,likeCount),recordingDetails(location))',
        'maxResults' => '50',
        'id' => $idString
    ]);
    $videoUrl = "https://www.googleapis.com/youtube/v3/videos?" . $videoQuery;
    curl_setopt($ch, CURLOPT_URL, $videoUrl);
    $resultVideo = curl_exec($ch);
    $jsonVideo = json_decode($resultVideo, true);

    if ($debug) {
        echo "GET request to " . $videoUrl . " \n";
    }

    $videosAddedCount = 0;
    foreach ($jsonVideo["items"] as $item) {
        $videosAddedCount++;
        $video = new Video();
        // always present
        $video->setId($item["id"]);
        $video->setTitle($item["snippet"]["title"]);
        $video->setDescription($item["snippet"]["description"]);
        $video->setViewCount($item["statistics"]["viewCount"]);
        $video->setLikeCount($item["statistics"]["likeCount"]);
        $video->setThumbnails($item["snippet"]["thumbnails"]);
        $video->setAuthor($item["snippet"]["channelTitle"]);
        $video->setChannelId($item["snippet"]["channelId"]);

        // might not be present
        if (isset($item["recordingDetails"]["location"])) {
            $video->setLocation($item["recordingDetails"]["location"]);
        }else{
            $video->setLocation("unknown");
        }
        
        // needs processing
        $video->setPublishedAt(strtotime($item["snippet"]["publishedAt"]));
        
    }
}

?>
