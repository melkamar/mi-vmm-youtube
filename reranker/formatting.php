<?php

include_once 'classes/Video.php';

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
 * @param \Video[] $resultCollection Collection of Video object to be printed
 */
function printSimpleOutput($resultCollection, $reranked) {
    //printVideoPlayerDescription($resultCollection, $reranked);
    // table header
    echo "<table class=\"table table-striped\">\n<thead><tr><th>#</th><th>Preview</th><th>Info</th></tr></thead>\n";
    //table row for every item
    foreach ($resultCollection as $item) {
        echo "<tr><td class=\"text-center\">" . $item->getResultStanding() . "</td>\n";
//        if ($reranked) {
//            echo "<td><div id=\"video-re-" . $item->getId() . "\"></div></td>\n";
//        } else {
//            echo "<td><div id=\"video-" . $item->getId() . "\"></div></td>\n";
//        }
        echo "<td><a href=\"https://www.youtube.com/watch?v=" . $item->getId() . "\" title=\"" . $item->getTitle() . "\">"
                . "<img src=\"".$item->getThumbnails()["medium"]["url"]."\" height=\"90px\"></a></td>\n";
        echo "<td><ul class=\"list-unstyled\"><li><a href=\"https://www.youtube.com/watch?v=" . $item->getId() . "\" title=\"" . $item->getTitle() . "\">";
        if (strlen($item->getTitle()) > 45) {
            echo substr($item->getTitle(), 0, 42) . "...</a></li>\n";
        } else {
            echo $item->getTitle() . "</a></li>\n";
        }
        echo "<li>" . secondsToDuration($item->getLength()) . "&nbsp;|&nbsp;".date("j.n.Y",$item->getPublishedAt())."</li>\n";
        echo "<li>Views:&nbsp;" . $item->getViewCount() 
                . "&nbsp;&nbsp;&nbsp;<img src=\"http://3guys.cz/mi-vmm/reranker/img/like.png\" title=\"Likes count\">&nbsp;".$item->getLikeCount()
                . "&nbsp;<img src=\"http://3guys.cz/mi-vmm/reranker/img/dislike.png\" title=\"Disikes count\">&nbsp;".$item->getDislikeCount()."</li>\n";
        echo "<li><a href=\"https://www.youtube.com/channel/" . $item->getChannelId() . "\" title=\"" . $item->getAuthor() . "\">";
        if (strlen($item->getAuthor()) > 45) {
            echo substr($item->getAuthor(), 0, 42) . "...</a></li></ul></td></tr>\n";
        } else {
            echo $item->getAuthor() . "</a></li></ul></td></tr>\n";
        }
    }
    echo "</table>\n";
}

?>
