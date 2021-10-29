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