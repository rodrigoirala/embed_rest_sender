<?php

require_once dirname(__FILE__) . '/RestProcessor.php';
require_once dirname(__FILE__) . '/StatementProcessor.php';
require_once dirname(__FILE__) . '/impl/concrete/FinanciadorPlanMedicoPersonaSender.php';
/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
class HttpSilentlySender {
    private static $instance = NULL;//singleton
    
    private $hadErrorStatus;
    private $arrayIds;
    private $stmProcessor;
    
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new HttpSilentlySender();
        }
        return self::$instance;
    }
    
    public function getRecordsData($stm){
        /*
        *Cuando se ejecuta esta funcion se debe instanciar el la clase StatementRestProcessor 
        * y setearle el atriburo donde se almacena el statement que se está por ejecutar. 
        * Una vez que se ejecuto el statement y el resultado es válido hay que procesar 
        * la data actualizada mediante el cliente rest que envia las novedades.
        */
       try {
           $this->stmProcessor = StatementProcessor::getInstance();
           $this->stmProcessor->setStatement($stm);
           $operation = $this->stmProcessor->getOperationType();

           $this->arrayIds = null;
           if (($operation == "delete") || ($operation == "update")){
               $this->arrayIds = $this->stmProcessor->getIds();
           }
           $this->hadErrorStatus = false;
       } catch (\Exception $e) {
           $this->hadErrorStatus = true;
           /* Si hay una exepcion hay que silenciarla para que el metodo continue y devuelva el resultado esperado
            * ademas hay que loguear el resultado fallido*/                
       }
    }
    
    public function sendData( $stmExcResult){
        if (isset($this->stmProcessor) && ($this->hadErrorStatus == false)) {
            if (($this->stmProcessor->getOperationType() == "insert") ){
                $this->arrayIds = $this->stmProcessor->getIds();
            }

            /* la funcion mysql_query devuelve true en caso que sea satistfactoria la ejecucion de una
             * sentencia INSERT, UPDATE o DELETE. 
             * Si se tiene un resultado true entonces se debe llamar al metodo que ejecuta el cliente Rest 
             * para enviar los datos.*/
            try {
                if ( $stmExcResult && ($this->arrayIds!=null) && (count($this->arrayIds) > 0)){
                    $restProcessor = RestProcessor::getInstance();
                    $restProcessor->init($this->stmProcessor->getDatabaseDotTableNoAlias(), $this->stmProcessor->getOperationType(), $this->arrayIds);
                    $restProcessor->sendNews();
                }
            } catch (\Exception $e) {
                /* Si hay una exepcion hay que silenciarla para que el metodo continue y el método que lo llama devuelva el resultado esperado
                 * ademas hay que loguear el resultado fallido y  
                 * almacenar el json que se genera para enviar mediante cliente rest */
            }
        }
    }
}

