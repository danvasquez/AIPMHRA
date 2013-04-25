<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 4/23/13
 * Time: 10:11 PM
 * To change this template use File | Settings | File Templates.
 */

require_once("./dompdf/dompdf_config.inc.php");

class PDFCreator {

    protected $fileString;
    protected $testString;
    protected $fileName;
    protected $domPDF;

    function __construct($fileString,$fileName){
        $this->fileString = $fileString;
        $this->fileName = "upload/".rand(100,300).$fileName;
    }

    public function GetPDF(){

        $this->domPDF = new DOMPDF();
        $this->domPDF->set_protocol("http://");
        $this->domPDF->set_host("healthylife.com");
        $this->domPDF->set_base_path("/ha/");
        $this->domPDF->load_html($this->fileString);
        $this->domPDF->render();
        try{
            file_put_contents($this->fileName,$this->domPDF->output());
            return $this->fileName;
        }catch(Exception $e){

        }
    }
}
