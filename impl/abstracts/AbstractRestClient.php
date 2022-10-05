<?php

require_once dirname(__FILE__) . '/../parsers/SenderUriParser.php';
require_once dirname(__FILE__) . '/../parsers/RestApiDataParser.php';
require_once dirname(__FILE__) . '/../../libs/JsonValidator.php';
require_once dirname(__FILE__) . '/../../HttpResponseCodes.php';
require_once dirname(__FILE__) . '/../../OperacionIntegracion.php';
require_once dirname(__FILE__) . '/../../OperacionesIntegracionRepository.php';
require_once dirname(__FILE__) . '/../../libs/RestCurlClient.php';

include_once dirname(__FILE__) . "/../somepath/DBConnection.php";

class JsonResponseException extends \Exception {
    
};

class JsonUpdateException extends \Exception {
    
};

/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
abstract class AbstractRestClient {

    const fileSchema = "";

    protected $dbConnection; //conexion a la base de datos
    protected $id; // id de la tabla donde se hace el insert, update o delete.
    protected $dataConverter; // converter que se utiliza para pasar los datos de las entidades y en base a la cual se genera el json para enviar.
    protected $uriesData; //objeto que parsea el archivo ini donde se relaciona a cada clase con la uri a la que debe enviar los datos
    protected $restData; //objeto que parsea el el archivo ini donde se almacenan los datos del servicio rest
    protected $stdClassData; //atributo donde se almacenan los datos cuando se recuperan desde la base de datos 

    public function init() {

        $this->dbConnection = DBConnection::getInstance();
        $this->uriesData = SenderUriParser::getInstance();
        $this->restData = RestApiDataParser::getInstance();
        if ($this->uriesData == false) {
            throw new \Exception("No se encuentra el archivo sender_uri.ini");
        }
        if ($this->restData == false) {
            throw new \Exception("No se encuentra el archivo rest_api.ini");
        }
    }

    public function setId($aId) {
        $this->id = $aId;
    }

    public function getId() {
        return $this->id;
    }

    /**
     * Devuelve el id del objeto concreto que se envia en los metodos PUT y DELETE.
     * Este metodo en el rismi1 debe ser implementado por cada subclase, ya que depende de la subclase 
     * para enviar el id que corresponda.
     * @return type
     */
    protected abstract function getIdentifierForUri();

    /**
     * Devuelve el string con el path y nombre del archivo de la constante fileSchema.
     */
    public abstract function getFileSchema();

    /**
     * Metodo que se encarga de setear al atributo $stdClassData los datos 
     * que correspondan segun el id del obje to a representar y segun correspondan
     * con la especificacion de la interfaz json.
     */
    protected abstract function loadStdClassData();

    /**
     * Devuelve la representacion json de $stdClassData en string.
     */
    protected function getJsonRepresentation() {
        return json_encode($this->stdClassData);
    }

    /**
     * Devuelve la uri del metodo post para la clase implementa la clase abstracta
     * @return type
     */
    protected function getPOSTUri() {
        return $this->uriesData->getUriForClass("POST", get_class($this));
    }

    /**
     * Devuelve la uri del metodo put para la clase implementa la clase abstracta
     * @return type
     */
    protected function getPUTUri() {
        $uri = $this->uriesData->getUriForClass("PUT", get_class($this));
        $uri = str_replace(":id", $this->getIdentifierForUri(), $uri);
        return $uri;
    }

    /**
     * Devuelve la uri del metodo del para la clase implementa la clase abstracta
     * @return type
     */
    protected function getDELUri() {
        $uri = $this->uriesData->getUriForClass("DEL", get_class($this));
        $uri = str_replace(":id", $this->getIdentifierForUri(), $uri);
        return $uri;
    }

    /**
     * Cuando se envia un HTTP DEL a la rest api devuelve datos en el body de la respuesta.
     * Esa procesa se procesa de manera separada por cada subclase que genera una instancia.
     */
    protected abstract function proceessBodyDELResponse($rsp);

    /**
     * Cuando se envia un HTTP PUT a la rest api devuelve datos en el body de la respuesta.
     * Esa procesa se procesa de manera separada por cada subclase que genera una instancia.
     */
    protected abstract function proceessBodyPUTResponse($rsp);

    /**
     * Cuando se envia un HTTP POST a la rest api devuelve datos en el body de la respuesta.
     * Esa procesa se procesa de manera separada por cada subclase que genera una instancia.
     */
    protected abstract function proceessBodyPOSTResponse($rsp);

    protected function saveOperationRequest(RestCurlClient $prepRequest) {
        $opIntegracion = new OperacionIntegracion();

        $opIntegracion->setEntityName(get_class($this));
        $opIntegracion->setHttpSolicitudBody($prepRequest->getRequestBody());
        $opIntegracion->setMetodoHttp($prepRequest->getHttpRequestMethod());
        $opIntegracion->setUri($prepRequest->getRequestUrl());
        $opIntegracion->setIntegradoOk(false); //va hardcodeado el false porque este valor se setea cuando se recibe el response.

        try {
            $opRep = new OperacionesIntegracionRepository();
            $opRep->insertOperacion($opIntegracion);
            return $opIntegracion;
        } catch (OptimisticLockException $e) {
            throw $e;
        }
    }

    protected function updateOperationResponse(RestCurlClient $response, OperacionIntegracion $opIntegracion) {

        $success = HttpResponseCodes::isSuccessCode($response->getResponseCode());
        $opIntegracion->setIntegradoOk($success);
        $opIntegracion->setHttpRespuestaBody(json_encode($response->getResponseBody()));
        $opIntegracion->setHttpRespuestaEstado($response->getResponseCode());

        try {
            $opRep = new OperacionesIntegracionRepository();
            $opRep->updateOperacion($opIntegracion);
            return true;
        } catch (OptimisticLockException $e) {
            return false;
        }
    }

    private function sendSavedTimeOutRequest() {
        $opIntegRep = new OperacionesIntegracionRepository();
        $operas = $opIntegRep->getOperacionesIntegracionByTimeOut();
        foreach ($operas as $op) {

            $request = new RestCurlClient();
            $request->setRequestUrl($this->restData->getUrl() . $op->getUri());

            if ($op->getMetodoHttp() == "GET") {
                $request->setHttpRequestMethod(HttpRequestMethod::$GET);
            }

            if ($op->getMetodoHttp() == "POST") {
                $request->setHttpRequestMethod(HttpRequestMethod::$POST);
            }

            if ($op->getMetodoHttp() == "PUT") {
                $request->setHttpRequestMethod(HttpRequestMethod::$PUT);
            }

            if ($op->getMetodoHttp() == "DEL") {
                $request->setHttpRequestMethod(HttpRequestMethod::$DELETE);
            }

            $request->setHeaders(array(
                'X-id-sistema-externo' => 'someSW',
                'X-id-operacion-externa' => $op->getId(),
            ));
            $request->setBody($op->getHttpSolicitudBody());
            $responseCode = $this->sendRequest($request);
            $this->updateOperationResponse($request, $op);
        }
    }

    /* Envia la request */

    private function sendRequest(RestCurlClient $request) {
        try {

            //$request->authenticateWith('username', 'password');
            $request->sendRequest();
        } catch (\Exception $e) {
            return 444; //no response
        }

        return $request->getResponseCode();
    }

    /* Este método se encarga de enviar y salvar todas las solicitudes que se envian 
     * al servicio rest, además si la request es enviada y procesada correctamente, 
     * se encarga de enviar las requests que fallaron hasta la request actual.
     */

    private function proccessRequest(RestCurlClient $prepRequest) {

        try {

            /* Primero se guarda la operacion que asociada a al solicitud.
             * Despues se envia el request y por ultimo se actualiza el registro 
             * asociada a la operacion enviada con los nuevos datos provenientes 
             * del response.
             */
            $opIntegracion = $this->saveOperationRequest($prepRequest);
            $prepRequest->addHeaders(array(
                'X-id-sistema-externo: Horrific and messy webapp',
                'X-id-operacion-externa: '. $opIntegracion->getId(),
            ));

            $responseCode = $this->sendRequest($prepRequest);
            $this->updateOperationResponse($prepRequest, $opIntegracion);

            if (HttpResponseCodes::isSuccessCode($responseCode)) {
                $this->sendSavedTimeOutRequest();
            }

            return true;
        } catch (OptimisticLockException $e) {
            throw $e;
        }
    }

    public function sendDEL() {
        try {

            $prepRequest = new RestCurlClient();
            $prepRequest->setRequestUrl($this->restData->getUrl() . $this->getDELUri());
            $prepRequest->setHttpRequestMethod("DELETE");

            $response = $this->proccessRequest($prepRequest);
            return $this->proceessBodyDELResponse($response);
        } catch (\Exception $e) {
            $this->dbCon->rollbackTransaction();
            throw new JsonUpdateException("Hubo un error al eliminar los datos");
        }
    }

    public function sendPOST() {
        try {
            $this->loadStdClassData();

            if ($this->stdClassData) {
                $validator = new JsonValidator($this->getFileSchema());
                $validator->validate($this->stdClassData);

                $jsonData = $this->getJsonRepresentation();

                $prepRequest = new RestCurlClient();
                $prepRequest->setRequestUrl($this->restData->getUrl() . $this->getPOSTUri());
                $prepRequest->setRequestBody($jsonData);
                $prepRequest->setHttpRequestMethod("POST");

                $response = $this->proccessRequest($prepRequest);
                return $this->proceessBodyPOSTResponse($response);
            }
        } catch (ValidationException $e) {
            throw new JsonUpdateException('Los datos recibidos no son validos o falta alguno requerido. ' . $e->getMessage());
        } catch (SchemaException $e) {
            throw new JsonUpdateException('El formato de los datos recibidos no es valido. ' . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function sendPUT() {
        try {

            $this->loadStdClassData();
            if ($this->stdClassData) {
                $validator = new JsonValidator($this->getFileSchema());
                $validator->validate($this->stdClassData);
                $jsonData = $this->getJsonRepresentation();

                $prepRequest = new RestCurlClient();
                $prepRequest->setRequestUrl($this->restData->getUrl() . $this->getPUTUri());
                $prepRequest->setRequestBody($jsonData);
                $prepRequest->setHttpRequestMethod("PUT");

                $response = $this->proccessRequest($prepRequest);
                return $this->proceessBodyPUTResponse($response);
            }
        } catch (ValidationException $e) {
            throw new JsonUpdateException('Los datos recibidos no son validos o falta alguno requerido. ' . $e->getMessage());
        } catch (SchemaException $e) {
            throw new JsonUpdateException('El formato de los datos recibidos no es valido. ' . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
