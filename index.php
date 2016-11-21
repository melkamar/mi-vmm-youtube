





<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>YouTube metadata re-ranking</title>
    </head>
    <body>
        <?php
        include_once './core.php';
        echo "<pre>";
        $pokus = fetchSearchResult("prase");

        foreach ($pokus as $kus){
            echo $kus->getTitle()." - ".$kus->getLength()." s - ".$kus->getPublishedAt()."\n";
        }
        echo "</pre>";
        
        ?>
    </body>
</html>
