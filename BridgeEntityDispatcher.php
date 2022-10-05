<?php

require_once dirname(__FILE__) . '/impl/abstracts/AbstractRestClient.php';
require_once dirname(__FILE__) . '/impl/concrete/SomeTableGetter.php';

/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
class BridgeEntityDispatcher {

    private static $instance = NULL;//singleton
    
    private $tablesSenders; //parseado del archivo ini donde se idenfitica 
            //que tabla del proyecto es tratada por cual implementacion de la integracion
    private $databaseTable; 
    private $reflectedClass; //clase del objeto que implementa el cliente rest
    private $objectImp; //instancia del objeto que implementa los métodos de envio de mensajes mediante http

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new BridgeEntityDispatcher();
        }
        return self::$instance;
    }

    function init($table) {
        try {
            
            $this->databaseTable = $table;
            $this->tablesSenders = parse_ini_file(dirname(__FILE__) ."/Resources/config/table_sender.ini", true);

            if ($this->databaseTable != ""){
                $this->reflectedClass = $this->getSenderForPersistedEntity("rest_senders", $this->databaseTable);

                if (isset($this->reflectedClass)) {
                    $className = $this->reflectedClass;
                    $reflectionHandler =  new ReflectionClass($className);
                    $this->objectImp = $reflectionHandler->newInstance();
                    $this->objectImp->init();
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    private function getSenderForPersistedEntity($section, $key) {
        if (array_key_exists($section, $this->tablesSenders)) {
            if (array_key_exists($key, $this->tablesSenders[$section]) && isset($this->tablesSenders[$section][$key])) {
                return $this->tablesSenders[$section][$key];
            }
        }
        return null;
    }
    
    public function getObjectImplementation(){
        return $this->objectImp;
    }
}
