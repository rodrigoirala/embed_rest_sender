<?php
require_once dirname(__FILE__) . '/BridgeEntityDispatcher.php';

/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
class RestProcessor {
    private static $instance = NULL;//singleton
    
    private $objectImpl;
    private $operation;
    private $arrayIds;
    private $tableName;
    private $reflectionMethod;
    
    public static function getInstance() {
        
        if (!isset(self::$instance)) {
            self::$instance = new RestProcessor();
        }
        return self::$instance;
    }
    
    function init($table, $operation, $arrayIds) {
        try {
            $bridgeDisp = BridgeEntityDispatcher::getInstance();
            $this->tableName = $table;
            $bridgeDisp->init($this->tableName);
            $this->objectImpl = $bridgeDisp->getObjectImplementation();
            $this->operation = $operation;
            $this->arrayIds = $arrayIds;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function sendNews() {
        if ($this->operation == "insert"){
            $this->reflectionMethod = new ReflectionMethod(get_class($this->objectImpl), 'sendPOST');
            //$this->objectImpl->sendPOST();
        }
        
        if ($this->operation == "update"){
            $this->reflectionMethod = new ReflectionMethod(get_class($this->objectImpl), 'sendPUT');
            //$this->objectImpl->sendPUT();
        }
        
        if ($this->operation == "delete"){
            $this->reflectionMethod = new ReflectionMethod(get_class($this->objectImpl), 'sendDEL');
            //$this->objectImpl->sendDEL();
        }
        
        //por cada id se envia una solicitud http
        foreach($this->arrayIds as $id){
            $this->objectImpl->setId($id);
            $this->reflectionMethod->invoke($this->objectImpl);//invoca el metodo segun la operacion a realizar
        }
    }
    
}
