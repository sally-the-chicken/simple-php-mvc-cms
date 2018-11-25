<?php

class Util_File
{
    public static function convert_url($url)
    {
        $url = preg_replace('/[^a-zA-Z0-9\-\d\s]/', '', strtolower($url));
        $url = str_replace(' ', '-', $url);

        $pattern = '/[^\pL\pN$-_.+!*\'\(\)\,\{\}\|\\\\\^\~\[\]`\<\>\#\"\;\/\?\:\@\&\=\.]/u';
        $url = preg_replace($pattern, '', $url);
        return trim($url);
    }

}
