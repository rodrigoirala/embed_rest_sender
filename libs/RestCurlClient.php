<?php

class HttpServerException extends Exception {}
class RestClientException extends Exception {}

/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
class RestCurlClient {

    private $handle;
    private $httpRequestOptions;
    private $bodyRequest;
    private $httpRequestMethod;
    private $requestUrl;
    
    private $responseBody;
    private $responseCode;
    private $responseObject;
    private $responseInfo;

    function __construct() {
        $this->httpRequestOptions = array();
        $this->httpRequestOptions[CURLOPT_RETURNTRANSFER] = true;
        $this->httpRequestOptions[CURLOPT_FOLLOWLOCATION] = false;
        $this->httpRequestOptions[CURLOPT_TIMEOUT] = 30;
        $this->httpRequestOptions[CURLOPT_HEADER] = true;
        $this->httpRequestOptions[CURLOPT_HTTPHEADER] = array('Content-Type: application/json');

        $this->httpRequestMethod = null;
        $this->requestUrl = null;
    }

    public function setHttpRequestMethod($method) {
        $this->httpRequestMethod = $method;
        $this->httpRequestOptions[CURLOPT_POST] = true;
        $this->httpRequestOptions[CURLOPT_CUSTOMREQUEST] = $method;
    }

    public function getHttpRequestMethod() {
        return $this->httpRequestMethod;
    }

    public function addHeaders(array $headData) {
        foreach($headData as $data){
            array_push($this->httpRequestOptions[CURLOPT_HTTPHEADER], $data);
        }
    }

    public function setRequestBody($data_string) {
        $this->bodyRequest = $data_string;
        array_push($this->httpRequestOptions[CURLOPT_HTTPHEADER], 'Content-Length: ' . strlen($this->bodyRequest));
        $this->httpRequestOptions[CURLOPT_POSTFIELDS] = $this->bodyRequest;
    }

    public function getRequestBody() {
        return $this->bodyRequest;
    }
    
    public function setRequestUrl($url){
        $this->requestUrl = $url;
    }
    
    public function getRequestUrl(){
        return $this->requestUrl;
    }
    
    public function getResponseBody(){
        return $this->responseBody;
    }
    
    public function getResponseCode() {
        return $this->responseCode;
    }

    public function sendRequest() {
        $this->handle = curl_init($this->requestUrl );

        if (!curl_setopt_array($this->handle, $this->httpRequestOptions)) {
            throw new RestClientException("Error setting cURL request options");
        }

        $this->responseObject = curl_exec($this->handle);
        $this->responseInfo = curl_getinfo($this->handle);
        curl_close($this->handle);
        
        $this->responseCode = isset($this->responseInfo['http_code']) ? $this->responseInfo['http_code']: null;
        
        $header_size = curl_getinfo($this->handle,CURLINFO_HEADER_SIZE);
        $this->responseBody = substr( $this->responseObject, $header_size );
        
        return $this->responseObject;
    }

}
