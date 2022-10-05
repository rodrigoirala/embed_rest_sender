<?php

/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
class RestServer {
    
    private $restData;
    private $urlMain;
    private $user;
    private $pass;
    
    public function init() {
        $this->restData = parse_ini_file(getcwd() . "./config/rest_api.ini", true);        
        $this->urlMain = $this->getData("rest_api", "url_main");
        $this->user = $this->getData("rest_api", "user");
        $this->pass = $this->getData("rest_api", "pass");
    }
    
    private function getData($section, $key){
        if (array_key_exists($section, $this->restData)) {
            if (array_key_exists($key, $this->restData[$section]) && isset($this->restData[$section][$key])) {
                return $this->restData[$section][$key];
            }
        }
    }
    
    function getUrlMain(){
        return $this->url_main;
    }
    
    function getUser(){
        return $this->user;
    }
    
    function getPass(){
        return $this->pass;
    }
    
}
