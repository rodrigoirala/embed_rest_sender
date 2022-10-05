<?php

/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
class OperacionIntegracion
{

    private $id;
    private $entityName;
    private $integradoOk;
    private $uri;
    private $httpSolicitudBody;
    private $solicitudId;
    private $metodoHttp;
    private $httpRespuestaEstado;
    private $httpRespuestaBody;
    private $creado;
    private $creadoPor;

    /**
     * Constructs a new instance
     */
    public function __construct()
    {
    }


    public function __toString()
    {
        return (string) $this->getEntityName();
    }

    /** GETTERS **/
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get entityName
     *
     * @return string 
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Get integradoOk
     *
     * @return boolean 
     */
    public function getIntegradoOk()
    {
        return $this->integradoOk;
    }

    /**
     * Get uri
     *
     * @return text
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Get httpSolicitudBody
     *
     * @return text
     */
    public function getHttpSolicitudBody()
    {
        return $this->httpSolicitudBody;
    }

    public function getSolicitudId()
    {
        return $this->solicitudId;
    }

    /**
     * Get metodoHttp
     *
     * @return string
     */
    public function getMetodoHttp()
    {
        return $this->metodoHttp;
    }

    /**
     * Get httpRespuestaEstado
     *
     * @return int
     */
    public function getHttpRespuestaEstado()
    {
        return $this->httpRespuestaEstado;
    }

    /**
     * Get httpRespuestaBody
     *
     * @return text
     */
    public function getHttpRespuestaBody()
    {
        return $this->httpRespuestaBody;
    }

    /**
     * Get creado
     *
     * @return \DateTime 
     */
    public function getCreado()
    {
        return $this->creado;
    }

    /**
     * Get creadoPor
     *
     * @return \Application\Sonata\UserBundle\Entity\User 
     */
    public function getCreadoPor()
    {
        return $this->creadoPor;
    }

    /** SETTERS **/

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set entityName
     * @param string $entityName
     * @return OperacionIntegracion 
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
        return $this;
    }

    /**
     * Set integradoOk
     * @param boolean $integradoOk
     * @return OperacionIntegracion 
     */
    public function setIntegradoOk($integradoOk)
    {
        $this->integradoOk = $integradoOk;
        return $this;
    }

    /**
     * Set uri
     * @param text $uri
     * @return OperacionIntegracion
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * Set httpSolicitudBody
     * @param text $httpBody
     * @return OperacionIntegracion
     */
    public function setHttpSolicitudBody($httpBody)
    {
        $this->httpSolicitudBody = $httpBody;
        return $this;
    }

    public function setSolicitudId($id)
    {
        $this->solicitudId = $id;
        return $this;
    }

    /**
     * Set metodoHttp
     * @param string $metodoHttp
     * @return OperacionIntegracion
     */
    public function setMetodoHttp($metodoHttp)
    {
        $this->metodoHttp = $metodoHttp;
        return $this;
    }

    /**
     * Set httpRespuestaEstado
     * @param int $httpRespuestaEstado
     * @return OperacionIntegracion
     */
    public function setHttpRespuestaEstado($httpRespuestaEstado)
    {
        $this->httpRespuestaEstado = $httpRespuestaEstado;
        return $this;
    }

    /**
     * Set httpRespuestaBody
     * @param text $httpRespuestaBody
     * @return OperacionIntegracion
     */
    public function setHttpRespuestaBody($httpRespuestaBody)
    {
        $this->httpRespuestaBody = $httpRespuestaBody;
        return $this;
    }

    /**
     * Set creado
     *
     * @param \DateTime $creado
     * @return OperacionIntegracion
     */
    public function setCreado($creado)
    {
        $this->creado = $creado;
        return $this;
    }

    /**
     * Set creadoPor
     * @return OperacionIntegracion
     */
    public function setCreadoPor($creadoPor = null)
    {
        $this->creadoPor = $creadoPor;
        return $this;
    }
}
