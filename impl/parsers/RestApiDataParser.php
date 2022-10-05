<?php

/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
class RestApiDataParser {

    private static $instance = null;//singleton    
    
    private $restApi;
    private $url;
    private $user;
    private $pass;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new RestApiDataParser();
            self::$instance->init();
        }
        return self::$instance;
    }

    function init() {
        try {
            $this->restApi = parse_ini_file($this->getFilePath(), true);

            if ($this->restApi != false){
                $this->url = $this->getRestData("rest_api", "url_main");
                $this->user = $this->getRestData("rest_api", "user");
                $this->pass = $this->getRestData("rest_api", "pass");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    private function getRestData($section, $key) { 
        try {
            if (array_key_exists($section, $this->restApi)) {
                if (array_key_exists($key, $this->restApi[$section]) && isset($this->restApi[$section][$key])) {
                    return $this->restApi[$section][$key];
                }
            }
            return null;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    private function getFilePath(){
        return dirname(__FILE__) .  "/../../Resources/config/rest_api.ini";
    }
    
    public function getUrl(){
        if (!isset($this->restApi)){
            $this->init();
        }
        return $this->url;
    }
    
    public function getUser(){
        if (!isset($this->restApi)){
            $this->init();
        }
        return $this->user;
    }
    
    public function getPass(){
        if (!isset($this->restApi)){
            $this->init();
        }
        return $this->pass;
    }
}
