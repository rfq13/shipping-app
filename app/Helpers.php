<?php


if (!function_exists('randomNumber')) {
    function randomNumber($length) {
        $result = '';

        for($i = 1; $i < $length+1; $i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }
}

if (!function_exists('getUrlParamsfromStr')) {
    function getUrlParamsfromStr($url) {
        $url_components = parse_url($url);
        parse_str($url_components['query'], $params);
        return $params;
    }
}