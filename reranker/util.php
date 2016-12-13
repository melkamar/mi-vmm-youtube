<?php
/**
 * Created by IntelliJ IDEA.
 * User: Martin
 * Date: 06.12.2016
 * Time: 20:39
 */

function debug_log($text)
{
    file_put_contents("log.log", $text . "\n", FILE_APPEND);
//    echo $text."<br />";
}

function erase_log(){
    file_put_contents("log.log", "");
}

function print_query_param($params, $field, $default){
   echo isset($params[$field]) ? htmlspecialchars($params[$field]) : $default;
}

function get_query_param($params, $field, $default){
    return isset($params[$field]) ? htmlspecialchars($params[$field]) : $default;
}