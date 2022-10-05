<?php

require_once dirname(__FILE__) . '/../somepath/DBConnection.php';

/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
class StatementProcessor {
    
    private static $instance = NULL;//singleton
    
    private $statement;
    private $operationType; //determina si se trata de un insert, update, o delete.
    private $conditions; //almacena la condicion que se ejecuta en el update o delete.
    private $ids;  //array de los ids a los que afecta el statement
    private $databaseDotTable;
    private $databaseDotTableNoAlias;
    
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new StatementProcessor();
        }
        return self::$instance;
    }
    
    public function setStatement($stm) {
        $this->statement = strtolower(trim($stm));
        $this->setOperationType();
    }
    
    public function getDatabaseDotTable(){
        return $this->databaseDotTable;
    }
    
    public function getDatabaseDotTableNoAlias(){
        return $this->databaseDotTableNoAlias;
    }
    
    public function getConditions(){
        return $this->conditions;
    }
    
    public function getOperationType(){
        return $this->operationType;
    }
    
    public function getIds(){
        if (($this->ids==null || count($this->ids) == 0) && ($this->statement!=null)){
            $this->prepareIds();
        }
        return $this->ids;
    }
    
    private function prepareIds(){
        $this->ids = array();
        $firstStmPortion = $this->getStatementFirstPortion();
        $this->setDatabaseTableName($firstStmPortion);
        
        if (($this->databaseDotTableNoAlias != null) && ($this->databaseDotTableNoAlias != "")) {
            $columnId = $this->getIdColumnName();
            $this->setConditions();
            
            $con = DBConnection::getInstance();
            $rstDBName = $con->executeQuery("SELECT " . $columnId . "
                                            FROM " . $this->databaseDotTable . "
                                            WHERE ". $this->conditions ." ");
            if ($rstDBName){
                $i = 0;
                foreach ($rstDBName  as $row) {
                    $this->ids[$i] = $row[$columnId];
                    $i++;
                }
            }
        }
    }
    
    private function getIdColumnName(){
        $con = DBConnection::getInstance();
        $colName = "";
        $rstDBName = $con->executeQuery("SELECT COLUMN_NAME  
                                FROM INFORMATION_SCHEMA.COLUMNS 
                                WHERE concat_ws('.', TABLE_SCHEMA,TABLE_NAME) = $this->databaseDotTableNoAlias  
                                    AND COLUMN_KEY='PRI' 
                                GROUP BY COLUMN_NAME ");
        if ($rstDBName){
            $colName = $rstDBName[0]['COLUMN_NAME'];
        }
        
        return $colName;
    }
    
    public function setOperationType(){
        $sentenceType = substr($this->statement, 0, 6);
        $this->operationType = $sentenceType;
    }
    
    /**
     * Retorna el statement desde el principio hasta la porcion donde se comienzan a setear los datos en el stm.
     * Ejemplo: "INSERT INTO dominio_interno (id, nombre, ...)..." devuelve "INSERT INTO dominio_interno" 
     */
    private function getStatementFirstPortion() {
        
        $this->setOperationType();
        $nextDelimiters = array();
        if ($this->operationType == "insert") {
            $nextDelimiters[0] = "(";
        }
        
        if ($this->operationType == "update") {
            $nextDelimiters[1] = "set";
        }
        
        if ($this->operationType == "delete") {
            $nextDelimiters[1] = "where";
            $nextDelimiters[2] = "*";
        }
        
        $minPos = 100; //se setea en un valor alto para que en la primera iteracion encuentre una posición menor a la actual
        foreach ($nextDelimiters as $deli) {
            if (strpos($this->statement, $deli) < $minPos ){
                $minPos = strpos($this->statement, $deli);
            }
        }
        
        if ($minPos == 100) {
            return false;
        }
        
        return trim(substr($this->statement, 0, $minPos));
    }
    
    /**
     * Recibe la pocion inicial de insert, update, delete hasta donde comiencen a setear los datos.
     * @param type $stmPortion
     * Retorna el nombre de la base con el nombre de la tabla de al forma ango_persona.dominio_interno.
     */
    private function setDatabaseTableName($stmPortion) {
        
        //ejemplo1: recibe "insert into blabli "
        //ejemplo2: recibe "delete from blabli"
        //ejemplo3: recibe "update blabli as rr"
        
        $tableName = $stmPortion; 
        $tableName = str_replace("insert", "", $tableName);
        //ejemplo1: obtiene " into blabli rr "
        //ejemplo2: obtiene " delete from blabli"
        //ejemplo3: obtiene " update blabli as rr"
        
        $tableName = str_replace("into", "", $tableName);
        //ejemplo1: obtiene "  blabli rr "
        //ejemplo2: obtiene "delete from blabli"
        //ejemplo3: obtiene "update blabli as rr"
        
        $tableName = str_replace("update", "", $tableName);
        //ejemplo1: obtiene "  blabli rr "
        //ejemplo2: obtiene "delete from blabli"
        //ejemplo3: obtiene " blabli as rr"
        
        $tableName = str_replace( "delete", "", $tableName);
        $tableName = str_replace("from", "", $tableName);
        $tableName = str_replace("*", "", $tableName);
        //ejemplo1: obtiene "  blabli rr "
        //ejemplo2: obtiene "  blabli"
        //ejemplo3: obtiene " blabli as rr"
        
        $tableName = str_replace( "'", "", $tableName);
        $tableName = str_replace("`", "", $tableName);
        
        $tableName = trim($tableName);
        //ejemplo1: obtiene "blabli rr"
        //ejemplo2: obtiene "blabli"
        //ejemplo3: obtiene "blabli as rr"
        
        $spacePosition = strpos($tableName, " ");
        $tableNameNoAlias = $tableName;
        if ($spacePosition!=false){
            $tableNameNoAlias = trim(substr ($tableName, 0, $spacePosition));
            //ejemplo1: obtiene "blabli"
            //ejemplo2: obtiene "blabli"
            //ejemplo3: obtiene "blabli"
        }
        
        $databaseTableName = $tableName; //la tabla con el alias sirve para casos de update y delete porque
                                         // si se puso un alias en la tabla tambien puede estar el alias en el where.
        $databaseTableNameNoAlias = $tableNameNoAlias;
        
        $dotPosition = strpos($tableName, "."); //chequea si viene el statement con el schema de la base de datos
        //si no encuentra el punto significa que en el statement esta solamente la tabla.
        //En este caso hay que agregar el nombre de la base de datos delante de la tabla
        if ($dotPosition==false) {
            $con = DBConnection::getInstance();
            $rstDBName = $con->executeQuery("SELECT table_schema 
                                FROM INFORMATION_SCHEMA.COLUMNS 
                                WHERE TABLE_NAME = $tableName 
                                group by table_schema ");
            if (count($rstDBName)>0){
                $databaseTableName = $rstDBName[0]['table_schema'] . "." . $tableName;
                $databaseTableNameNoAlias = $rstDBName[0]['table_schema'] . "." . $tableNameNoAlias;
            }
            
            //ejemplo1: obtiene "schema.blabli"
            //ejemplo2: obtiene "schema.blabli"
            //ejemplo3: obtiene "schema.blabli"
        }
        
        $this->databaseDotTable = $databaseTableName;
        $this->databaseDotTableNoAlias = $databaseTableNameNoAlias;
    }
    
    private function setConditions(){
        
        $this->conditions = "";
        
        //en caso que se trate de un insert obviamente se debe consultar despues de ejecutar el statement.
        //Por lo cual setea la condicion de que sea el ultimo id generado
        if (($this->operationType == "insert")) {
            $this->conditions = $this->getIdColumnName(). " = ";
            $this->conditions.= "(SELECT max(" . $this->getIdColumnName() . ") FROM " . $this->databaseDotTableNoAlias . " )";
        }

        //cuando se trata de un update o delete primero se debe setear la condicion para que se puedan consultar los datos 
        if (($this->operationType == "update") || ($this->operationType == "delete")) {
            $this->conditions = "update";
            $wherePos = strpos($this->statement, "where");
            if ($wherePos != false){
                $condi = substr($this->statement, $wherePos);
                $condi = str_replace("where", "", $condi);
                
                $this->conditions = $condi;
            }
        }
    }
}
