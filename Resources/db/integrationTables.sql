CREATE TABLE integ_operaciones_enviadas (
    id BIGINT NOT NULL PRIMARY KEY ,
    entidad_disparadora VARCHAR(60) NOT NULL COMMENT 'Es la tabla sobre la que se hace el insert, update o delete y que dispara el evento donde se envia la solicitud http',
    integrado_ok BOOLEAN NOT NULL COMMENT 'Indica si el proceso de integracion terminó bien o no',
    http_uri TEXT COMMENT 'Almacena la uri del servicio rest al cual se envia el http request',
    http_solicitud_body TEXT COMMENT 'Contiene el contenido del cuerpo de la request http, es decir, los datos en formato json enviados',
    metodo_http VARCHAR(10) NOT NULL COMMENT 'Se registra el método http que se envia. Es decir, GET, POST, PUT o DEL',
    http_respuesta_estado INT NULL COMMENT 'Se registra el estado del response http. Por ejemplo: 200, 201, 404, 500, etc',
    http_respuesta_body TEXT NULL COMMENT 'Se registra el cuerpo del response http. En caso de exito debería ser un json con los datos que envia la interfaz rest y en caso de fracaso se registra el mensaje de error',
    creado TIMESTAMP NOT NULL COMMENT 'la fecha en que se envia el paquete de informacion'
)ENGINE=InnoDB
COLLATE=utf8_unicode_ci;
;
