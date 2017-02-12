<?php
/**This is the API for the tasklist.
 * @author Merlin Becker
 * @version 1.0
 * @since 1.0
 * @created 30.01.2017
 * 
 * @todo refactor this class (define a class maybe or two...) a little bit
 * 
 * some thoughts on OAUTH Access and the Google API:
 * http://sitr.us/2011/08/26/cookies-are-bad-for-you.html
 * http://stackoverflow.com/questions/22949339/persistent-login-with-google-oauth2-using-the-php-client-library
 * http://enarion.net/programming/php/google-client-api/google-client-api-php/
 *
 * important facts about the refresh token#
 * @todo test the refresh token behavior
 * http://stackoverflow.com/questions/10827920/not-receiving-google-oauth-refresh-token
 * for testing issues log out and add &prompt=consent to the redirect url to get the refresh token
 * ***/

/**
 * headers: allow cross origin access
 * */
header("Access-Control-Allow-Origin: *");

/**
 * in every call, check if there is a properly set up server running
 * **/

require_once 'classes/installer.class.php';
require_once 'classes/database.class.php';
require_once 'classes/user.class.php';
$installer=Installer::sharedInstaller();

$output=array();
session_start();
$user=new User();


//this will be sent by the google oauth callback.. be sure to save it in the user
if(isset($_GET['code'])){
	$user->oauthenticate($_GET['code']);
	setcookie("ACCTOK",$user->refreshtoken,time()+604800); //keep the cookie 7 days
	header('Location: ' . filter_var("index.html", FILTER_SANITIZE_URL));
}
/**
 * log out user 
 * this should be only temporary
 * **/
else if(isset($_GET['logout'])){
	$user->log_out();
	header('Location: ' . filter_var("index.html", FILTER_SANITIZE_URL));
}

//start service
ob_start();
	//get data from client
	$data = json_decode(file_get_contents('php://input'));
	
	//first check for any installcommands. Assuming that there will be no access_token here.
	//@todo refactor this completely
	//do this just to get no notice from php
	
	
	if(isset($data->{"c"})){
	//COMMAND installLibs
		if($data->{"c"}=="installLibs"){
			$result=$installer->downloadLibs();
			if($result==0){
				$output['status']='success';
			}
			else{
				$output['status']='error';
				$output['errordescription']=$result;
			}
		}
		
		//COMMAND setCredentials
		else if($data->{"c"}=="setCredentials"){
			$credent=(array)$data->payload;
			
			foreach ($credent as $key=>$val){
				$installer->conf[$key]=trim($val);
			}
			
			$installer->saveConfig();
			$output['status']="success";
		}
	}else{
	$missing=$installer->checkInstallNeeds();
	if(count($missing)>0){
		$output['status']='error';
		$output['errordescription']='incomplete_installation';
		$output['payload']=$missing;
	}
	else{
		//authenticate the user
		$accesstoken="";
		if(isset($data->{"access_token"})) $accesstoken=$data->{"access_token"};
		
		$output=$user->log_in($accesstoken);	
 		
 		/**
 		 * @todo add real api cmds here 
 		 * */
		if($user->is_logged_in()){
		 if(isset($data->{"c"})&&$data->{"c"}=="logout"){ 
			$user->log_out();
		 }
		}
	}
	}
	
//output of the api	
	
$possible_errors=ob_get_contents();
ob_end_clean();
if(strlen($possible_errors)>0){
	$output['status']='error';
	if(!isset($output['errordescription']))$output['errordescription']="";
	if(is_array($output['errordescription']))
		$output['errordescription'][]=$possible_errors;
	else	
		$output['errordescription'].=$possible_errors;
	
}
echo json_encode($output);
//service ended

?>