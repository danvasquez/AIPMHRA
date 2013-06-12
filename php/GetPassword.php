<?php
/**
 * User: danvasquez
 * Date: 5/2/13
 * Time: 7:47 PM
 */

foreach (glob("classes/*.php") as $filename)
{
    require_once($filename);
}


$data = file_get_contents("php://input");
$objData = json_decode($data);

if(isset($objData)){
    $userID = $objData->sUserID;
}

if(!isset($userID)){
    $userID = "dan@maestroinfo.com";
}

if(isset($userID)){
    try{
        $user = new User();
        $password = $user->GetPassword($userID);
        if(isset($password)){
            //mail it to the id
		$mail = new PHPMailer();
		$mail->IsSMTP();  // telling the class to use SMTP
		$mail->Host     = "smtp.gmail.com"; // SMTP server
		$mail->SetFrom("vasquez.dan@gmail.com","Healthylife Admin");		
		$mail->SMTPAuth = true;
		
		$mail->Username = "vasquez.dan@gmail.com";
		$mail->Password = "danV6394";
		$mail->SMTPSecure = "ssl";
		$mail->Port = 465;
		$mail->AddAddress($userID);
		$mail->Subject  = "Your HRA Survey Password";
		            
		$mail->Body = "Please store this password in a safe place.\n Your Password is: $password";
		$mail->WordWrap = 50;

		if(!$mail->Send()){
			die($mail->ErrorInfo);
			echo 0;
		}else{
			echo 1;
		}          
        }

    }catch(Exception $e){
        echo -1;
    }
}else{
    echo 0;
}
