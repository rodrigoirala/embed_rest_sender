<?php

require_once dirname(__FILE__) . '/../somapath/DBConnection.php';
require_once dirname(__FILE__) . '/HttpResponseCodes.php';

/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
class OperacionesIntegracionRepository {
    
    private $con;
    
    function __construct() {
        $this->con = DBConnection::getInstance();
    }
    
    public function insertOperacion(OperacionIntegracion $op) { 
        
        if ($op) {
            try {
                $this->con->beginTransaction();
            
                $id = 0;
                $results = $this->con->executeQuery("SELECT max(id) as insertId FROM integ_operaciones_enviadas");
                if ($results != null) {
                    $id = $results[0]["insertId"];
                }
                $id++;

                $prep = $this->con->prepareStatement("INSERT integ_operaciones_enviadas 
                                (id, 
                                entidad_disparadora,
                                integrado_ok,
                                http_solicitud_body,
                                metodo_http,
                                http_respuesta_estado,
                                http_respuesta_body,
                                http_uri,
                                creado) 
                                VALUES 
                                (:ID,
                                :ENTIDAD_DISPA,
                                :INTE_OK,
                                :HTTP_BODY,
                                :HTTP_METHOD, 
                                :HTTP_RTA_ESTADO,
                                :HTTP_RTA_BODY,
                                :HTTP_URI,
                                :CREADO)");
                
                $prep->bindValue("ID", $id, PDO::PARAM_INT);
                $prep->bindValue("ENTIDAD_DISPA", $op->getEntityName(), PDO::PARAM_STR);
                $prep->bindValue("INTE_OK", $op->getIntegradoOk(), PDO::PARAM_BOOL);
                $prep->bindValue("HTTP_BODY", $op->getHttpSolicitudBody(), PDO::PARAM_STR);
                $prep->bindValue("HTTP_METHOD", $op->getMetodoHttp(), PDO::PARAM_STR);
                $prep->bindValue("HTTP_RTA_ESTADO", $op->getHttpRespuestaEstado(), PDO::PARAM_INT);
                $prep->bindValue("HTTP_RTA_BODY", $op->getHttpRespuestaBody(), PDO::PARAM_STR);
                $prep->bindValue("HTTP_URI", $op->getUri(), PDO::PARAM_STR);
                $prep->bindValue("CREADO", date(), PDO::PARAM_STR);
                
                $this->con->executeStatement($prep);
                $op->setId($id);
                $this->con->commit();
                return true;
            } catch (Exception $e) {
                $this->con->rollbackTransaction();
                return false;
            }
        }
    }
    
    public function updateOperacion(OperacionIntegracion $op) { 
        
        if ($op) {
            try {
                $this->con->beginTransaction();

                $prep = $this->con->prepareStatement("UPDATE integ_operaciones_enviadas 
                                SET entidad_disparadora =:ENTIDAD_DISPA , 
                                    integrado_ok = :INTE_OK,
                                    http_solicitud_body = :HTTP_BODY,
                                    metodo_http = :HTTP_METHOD,
                                    http_respuesta_estado = :HTTP_RTA_ESTADO,
                                    http_respuesta_body = :HTTP_RTA_BODY,
                                    http_uri = :HTTP_URI,
                                    creado = :CREADO 
                                WHERE id = :ID");
                
                $prep->bindValue("ID", $op->getId(), PDO::PARAM_INT);
                $prep->bindValue("ENTIDAD_DISPA", $op->getEntityName(), PDO::PARAM_STR);
                $prep->bindValue("INTE_OK", $op->getIntegradoOk(), PDO::PARAM_BOOL);
                $prep->bindValue("HTTP_BODY", $op->getHttpSolicitudBody(), PDO::PARAM_STR);
                $prep->bindValue("HTTP_METHOD", $op->getMetodoHttp(), PDO::PARAM_STR);
                $prep->bindValue("HTTP_RTA_ESTADO", $op->getHttpRespuestaEstado(), PDO::PARAM_INT);
                $prep->bindValue("HTTP_RTA_BODY", $op->getHttpRespuestaBody(), PDO::PARAM_STR);
                $prep->bindValue("HTTP_URI", $op->getUri(), PDO::PARAM_STR);
                $prep->bindValue("CREADO", date(), PDO::PARAM_STR);
                
                $this->con->executeStatement($prep);
                $this->con->commit();
            } catch (Exception $e) {
                $this->con->rollbackTransaction();
            }
        }
    }
    
    /**
     * @param int $idOperacion
     * @return array de OperacionIntegracion
     */
    public function getOperacionesIntegracionById($idOperacion){
        
        $prep = $this->con->prepareStatement("SELECT * 
                            FROM integ_operaciones_enviadas 
                            WHERE id = :ID");

        $prep->bindValue(":ID", $idOperacion,  PDO::PARAM_INT);
        $result = $this->con->executeStatement($prep);
        $operaciones  = $this->convertArrayResultsToObjects($result );
        return $operaciones;
    }
    
    /**
     * 
     * @return array de OperacionIntegracion
     */
    public function getOperacionesIntegracionWithErrors(){
        
        $prep = $this->con->prepareStatement("SELECT * 
                            FROM integ_operaciones_enviadas 
                            WHERE integrado_ok = :INT_OK");

        $prep->bindValue(":INT_OK", false,  PDO::PARAM_BOOL);
        $result = $this->con->executeStatement($prep);
        $operaciones  = $this->convertArrayResultsToObjects($result );
        return $operaciones;
    }
    
    /**
     * 
     * @return array de OperacionIntegracion
     */
    public function getOperacionesIntegracionByTimeOut(){
        
        $prep = $this->con->prepareStatement("SELECT * 
                            FROM integ_operaciones_enviadas 
                            WHERE integrado_ok = :INT_OK AND http_respuesta_estado IN (:RTA)");

        $prep->bindValue(":INT_OK", false,  PDO::PARAM_BOOL);
        $prep->bindValue(":RTA", "'" . implode("','". HttpResponseCodes::getTimeoutCodes()) . "'",  PDO::PARAM_STR);
        $success = $this->con->executeStatement($prep);
        $operaciones  = null;
        if ($success){
            $result = $prep->fetchAll();
            $operaciones  = $this->convertArrayResultsToObjects($result );
        }
        return $operaciones;
    }
    
    private function convertArrayResultsToObjects(Array $operaciones){
        
        $operacionesObject =  array();
        $i = 0;
        foreach ($operaciones as $ope) {
            $operacion = new OperacionIntegracion();
            $operacion->setId($ope['id']);
            $operacion->setEntityName($ope['entidad_disparadora']);
            $operacion->setIntegradoOk($ope['integrado_ok']);
            $operacion->setUri($ope['http_uri']);
            $operacion->setHttpSolicitudBody($ope['http_solicitud_body']);
            $operacion->setMetodoHttp($ope['metodo_http']);
            $operacion->setHttpRespuestaEstado($ope['http_respuesta_estado']);
            $operacion->setHttpRespuestaBody($ope['http_respuesta_body']);
            $operacion->setCreado($ope['creado']);
            $operacionesObject[$i++] = $operacion;
        }
        return  $operacionesObject;
    }
}