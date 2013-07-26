<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 2/8/13
 * Time: 9:11 PM
 * To change this template use File | Settings | File Templates.
 */
class RiskFactor
{
    public $idRiskFactorID = 0;
    public $idAnswerID = 0;
    public $sText = "Risk Factor Text";

    function RiskFactor($_riskFactorID){
        $this->idRiskFactorID  = $_riskFactorID;
    }

    public function ToJSON(){
        echo json_encode($this);
    }
}
