<?php

/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
class SenderUriParser {

    private static $instance = null;
    private $senderUri;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new SenderUriParser();
            self::$instance->init();
        }
        return self::$instance;
    }

    function init() {
        try {
            $this->senderUri = parse_ini_file($this->getFilePath(), true);
            if ($this->senderUri == false){
                throw new Exception("no se puede leer el archivo sender_uri.ini");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    private function getFilePath(){
        return dirname(__FILE__) . "/../../Resources/config/sender_uri.ini";
    }
    
    public function getUriForClass($section, $key) { 
        try {
            if ($this->senderUri==null) {
                $this->init();
            }

            if (array_key_exists($section, $this->senderUri)) {
                if (array_key_exists($key, $this->senderUri[$section]) && isset($this->senderUri[$section][$key])) {
                    return $this->senderUri[$section][$key];
                }
            }
            return null;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
