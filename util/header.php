<?php 

Class Util_Header {

    const HEADER_REDIRECT_RESPONSE_301 = 301;
    const HEADER_REDIRECT_RESPONSE_302 = 302;

    public static function redirect($location, $response_code = self::HEADER_REDIRECT_RESPONSE_302) {
        header('Location: ' . $location, true, $response_code);
        exit;
    }
}

?>