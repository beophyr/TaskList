<?php 
/**
* this is a wrapper class for alle user specific tasks
* capsules 
* @author Merlin Becker
* @version 1.0
* @created 11.02.2017
**/
require_once "classes/installer.class.php";
require_once "classes/database.class.php";

class User{
	//as user we need the settings
	private $settings;
	private $usesoauth;
	private $oauthclient;
	private $oauthservice;
	
	private $is_logged_in;
	private $db_controller;
	
	public $accesstoken;
	public $refreshtoken;
	
	public function __construct(){
		$this->is_logged_in=false;
		
		$settings=Installer::sharedInstaller();
	
		//setup the google oauth service, but only if it was checked during the installation of the system.
		//else we create a guest user
    if(isset($settings->conf['uses_g_api'])){   
			if($settings->conf['uses_g_api']=="yes"
					&&isset($settings->conf['g_client_id']
							,$settings->conf['g_client_secret']
							,$settings->conf['g_redirect_uri']
							,$settings->conf['g_simple_api_key'])
					&&is_dir(Installer::LIB_DIR."/".Installer::G_API_PATH."/"))
			{
			require_once Installer::LIB_DIR."/".Installer::G_API_PATH."/vendor/autoload.php";
			
			$client = new Google_Client();
			$client->setApplicationName("Tasklist Google OAUTH");
			$client->setClientId($settings->conf['g_client_id']);
			$client->setClientSecret($settings->conf['g_client_secret']);
			$client->setRedirectUri($settings->conf['g_redirect_uri']);
			$client->setAccessType("offline");
			$client->setDeveloperKey($settings->conf['g_simple_api_key']);
			$client->addScope("https://www.googleapis.com/auth/userinfo.email");
		
			//Send Client Request
			$this->usesoauth=true;
			$this->oauthclient=$client;
			$this->oauthservice=new Google_Service_Oauth2($client);
			}
			else{
				$this->usesoauth=false;
			}
		}
		else $this->usesoauth=false;
	}
	/**
	 * this function is for getting the access token and the refresh token from google api callback 
	 * **/
	public function oauthenticate($code){
		if($code=="guest"){
			$this->accesstoken=$_SESSION['access_token'] = "guest";
			$this->refreshtoken=$_SESSION['refresh_token']="guest";
		}
		else{
			$this->oauthclient->authenticate($code);
			$this->accesstoken=$_SESSION['access_token'] = $this->oauthclient->getAccessToken();
			$this->refreshtoken=$_SESSION['refresh_token']=$this->oauthclient->getRefreshToken();
		}
	}
	
	
	public function log_out(){
		unset($_SESSION['access_token']);
		$this->oauthclient->revokeToken();
		$output['status']='success';
		$output['payload']='logout successful';
	}
	
	private function getGuestUserObject(){
		$userData = new stdClass();
		$userData->id="guest";
		$userData->name='guest';
		$userData->email="guest@tasklist";
		$userData->link="#guest";
		$userData->picture="none";
		$userData->refresh_token="guest";
		
		$this->accesstoken=$_SESSION['access_token']='guest';
		$this->refreshtoken=$_SESSION['refresh_token']='guest';
		return $userData;
	}
	
	private function getOauthUserObject(){
		$oauthdata= $this->oauthservice->userinfo->get();	
		$userData = new stdClass();
		$userData->id=$oauthdata->id;
		$userData->name=$oauthdata->name;
		$userData->email=$oauthdata->email;
		$userData->link=$oauthdata->link;
		$userData->picture=$oauthdata->picture;
		$userData->refresh_token=$this->oauthclient->getRefreshToken();
		return $userData;
	}
	public function is_logged_in(){
		return $this->is_logged_in;
	}
	
	/**
	 * authentificates the user.
	 * @return array with 'status' and wether acces token or link to oauth url
	 * **/
	public function log_in($accesstoken){
 
		$output=array();
		$this->db_controller=new Database();
		
		$userData=false;
		/**if the accesstoken is guest**/
		if(!$this->usesoauth){;
			$userData=$this->getGuestUserObject();	
		}
		else{
			if(isset($_SESSION['access_token'])){
				if($_SESSION['access_token']=='guest'){
         			$userData=$this->getGuestUserObject();
          		}
				else{
					$this->oauthclient->setAccessToken($_SESSION['access_token']);
					$this->accesstoken=$_SESSION['access_token'];
				}
			}
			else if($accesstoken!=""){
				if($accesstoken=='guest')$userData=$this->getGuestUserObject();
				else{
					$this->oauthclient->refreshToken($accesstoken);
					$_SESSION['access_token']=$this->oauthclient->getAccessToken();
					$this->accesstoken=$_SESSION['access_token'];
				}
			}
			
			if(empty($userData)&&$this->oauthclient->getAccessToken()){
				$userData = $this->getOauthUserObject();
			}	
		}
				
				
		if(!empty($userData)){
			$this->db_controller->query("SELECT * FROM tl_users WHERE oauth_user_id = :oauth_user_id");
			$this->db_controller->bind(':oauth_user_id',$userData->id);
			$user=$this->db_controller->single();
			echo $this->db_controller->error;
			if(empty($user)){
				$this->db_controller->query("INSERT INTO tl_users (u_name,u_email,oauth_user_id,oauth_user_page,oauth_user_photo,last_login,refresh_token) VALUES(:u_name,:u_email,:oauth_user_id,:oauth_user_page,:oauth_user_photo,:last_login,:refresh_token)");
				$this->db_controller->bind(':u_name',$userData->name);
				$this->db_controller->bind(':u_email',$userData->email);
				$this->db_controller->bind(':oauth_user_id',$userData->id);
				$this->db_controller->bind(':oauth_user_page',$userData->link);
				$this->db_controller->bind(':oauth_user_photo',$userData->picture);
				$this->db_controller->bind(':last_login',time());
				$this->db_controller->bind(':refresh_token',$userData->refresh_token);
				$this->db_controller->execute();
				echo $this->db_controller->error;
			}
			
			//update login time
			$this->db_controller->query("UPDATE tl_users SET last_login=:last_login WHERE oauth_user_id=:oauth_user_id");
			$this->db_controller->bind(':last_login',time());
			$this->db_controller->bind(':oauth_user_id',$userData->id);
			$this->db_controller->execute();
			echo $this->db_controller->error;
			

			$output['status']="success";
			$output['access_token']=$userData->refresh_token;
			$this->is_logged_in=true;
			return $output;
		}	
	else{
		$output['status']='error';
		$output['errordescription']='not_logged_in';
		$output['payload']['login_url']=$this->oauthclient->createAuthUrl();
		$this->is_logged_in=false;
		return $output;
	}

}
	
}
?>