<?php

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

