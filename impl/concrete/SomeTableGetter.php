    <?php
require_once (dirname(__FILE__) . '/../abstracts/AbstractRestClient.php');

/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
class SomeTableGetter extends AbstractRestClient {

    const fileSchema =  "./../Resources/jsonSchemas/SomeTable.json";
    
    function __constructor(){
        $this->super->constructor();
    }
    
    public function getFileSchema() {
        return getcwd(). self::fileSchema;
    }

    protected function loadStdClassData() {
        
        //los nombres de la columna deben coincidir con el atriburo que se parsea a json.
        $rst = $this->dbConnection->executeQuery("SELECT * 
                            FROM some_table 
                            WHERE id= $this->id");
        
        if (count($rst)>0){
            $this->stdClassData = (object)$rst[0];
        }
    }

    protected function proceessBodyDELResponse($rsp) {
        
    }

    protected function proceessBodyPOSTResponse($rsp) {
        
    }

    protected function proceessBodyPUTResponse($rsp) {
        
    }

    protected function getIdentifierForUri() {
        return $this->getId();
    }
}

?>
