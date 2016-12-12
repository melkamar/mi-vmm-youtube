<?php

include_once 'classes/Video.php';

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

function secondsToDuration($seconds) {
    $sec = $seconds % 60;
    $min = (floor($seconds / 60)) % 60;
    $hrs = floor($seconds / 3600);
    $out = "";
    if ($hrs > 0) {
        $out += $hrs . ":";
    }
    $out = $out . str_pad($min, 2, '0', STR_PAD_LEFT) . ":";
    $out = $out . str_pad($sec, 2, '0', STR_PAD_LEFT);
    return $out;
}

/**
 * 
 * @param \Video[] $results Collection of results to be added to player
 */
function printVideoPlayerDescription($results, $reranked) {
    echo "<script>\n";
    echo "// Load the IFrame Player API code asynchronously.\n";
    echo "var tag = document.createElement('script');\n";
    echo "tag.src = \"https://www.youtube.com/player_api\";\n";
    echo "var firstScriptTag = document.getElementsByTagName('script')[0];\n";
    echo "firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);\n";

    echo "// Replace the 'ytplayer' element with an <iframe> and\n// YouTube player after the API code downloads.\n";
    echo "var player = new Array();\n";
    echo "function onYouTubePlayerAPIReady() {\n";
    foreach ($results as $result) {
        echo "player.push(new YT.Player('video-" . $result->getId() . "', {
        height: '90',
        width: '160',
        videoId: '" . $result->getId() . "'
        }));\n";
        echo "player.push(new YT.Player('video-re-" . $result->getId() . "', {
        height: '90',
        width: '160',
        videoId: '" . $result->getId() . "'
        }));\n";
    }
    echo "}\n</script>\n";
}

/**
 * 
 * @param \Video[] $resultCollection Collection of Video object to be printed
 */
function printSimpleOutput($resultCollection, $reranked) {
    printVideoPlayerDescription($resultCollection, $reranked);
    // table header
    echo "<table class=\"table table-striped\">\n<thead><tr><th>#</th><th>Video</th><th>Info</th></tr></thead>\n";
    //table row for every item
    foreach ($resultCollection as $item) {
        echo "<tr><td>" . $item->getResultStanding() . "</td>\n";
        if ($reranked) {
            echo "<td><div id=\"video-re-" . $item->getId() . "\"></div></td>\n";
        } else {
            echo "<td><div id=\"video-" . $item->getId() . "\"></div></td>\n";
        }
        echo "<td><ul><li><a href=\"https://www.youtube.com/watch?v=" . $item->getId() . "\" title=\"" . $item->getTitle() . "\">";
        if (strlen($item->getTitle()) > 35) {
            echo substr($item->getTitle(), 0, 32) . "...</a></li>\n";
        } else {
            echo $item->getTitle() . "</a></li>\n";
        }
        echo "<li>" . secondsToDuration($item->getLength()) . "</li>\n";
        echo "<li>" . $item->getViewCount() . "</li>\n";
        echo "<li><a href=\"https://www.youtube.com/channel/" . $item->getChannelId() . "\" title=\"" . $item->getAuthor() . "\">";
        if (strlen($item->getAuthor()) > 35) {
            echo substr($item->getAuthor(), 0, 32) . "...</a></li></ul></td></tr>\n";
        } else {
            echo $item->getAuthor() . "</a></li></ul></td></tr>\n";
        }
    }
    echo "</table>\n";
}

/**
 * Fetches first x result of search of given term and parses the JSON result into collection of \Video objects which is then returned 
 * @param integer $searchQuery Term that should be inserted into search
 * @return \Video[]
 */
function fetchSearchResult($searchQuery, $debug, $videoLimit) {
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
        if ($debug) {
            echo "GET request to " . $searchUrl . " \n";
        }

        //save all fetched IDs and make comma-separated string out of them
        $ids = array();
        $idStringTmp = "";
        foreach ($json["items"] as $item) {
            array_push($ids, $item["id"]["videoId"]);
            $idStringTmp = $idStringTmp . "," . $item["id"]["videoId"];
        }
        //remove first comma
        $idString = substr($idStringTmp, 1);

        if ($debug) {
            echo sizeof($ids) . " video ids fetched.\n";
            echo "idString = \"" . $idString . "\"\n";
        }

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

        if ($debug) {
            echo "GET request to " . $videoUrl . " \n";
        }

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

        if ($debug) {
            echo "Added " . $videosAddedCount . " videos to the collection\n";
        }

        if (isset($json["nextPageToken"])) {
            $nextPageToken = $json["nextPageToken"];
        } else {
            break;
        }
    }

    //return collection
    return $outputCollection;
}

?>
