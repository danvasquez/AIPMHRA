<?php

require_once('./classes/PDFCreator.php');

$data = file_get_contents("php://input");
$objData = json_decode($data);

if(!isset($objData->fileString)){
    $fileString = "nodata";
}else{
    $fileString = urldecode($objData->fileString);
}

$PDF = new PDFCreator($fileString,$objData->fileName);
echo $PDF->GetPDF();




