<?php
/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
class HttpResponseCodes {
    private static $HTTP_SUCCESS_CODES = array(200, 201, 202, 203, 204, 205, 206, 207, 208, 226);
    private static $HTTP_TIMEOUT_CODES = array(408, 419, 504, 522, 598, 599);
    
    public static function getSuccessCodes(){
        return self::$HTTP_SUCCESS_CODES;
    }
    
    public static function getTimeoutCodes(){
        return self::$HTTP_TIMEOUT_CODES;
    }
    
    public static function isErrorCode($code) {
        return !in_array($code, self::$HTTP_SUCCESS_CODES);
    }
    
    public static function isSuccessCode($code) {
        return in_array($code, self::$HTTP_SUCCESS_CODES);
    }
    
    public static function isTimeOutCode($code) {
        return in_array($code, self::$HTTP_TIMEOUT_CODES);
    }
}

?>
