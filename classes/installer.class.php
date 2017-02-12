<?php
/***
this is the installation file for this project
downloads the requirements and installs them
checks, if all login data is there

@author Merlin Becker
@version 1.0
@created 19.01.2017

***/


/**Class Installer
 * Class for checking, that all settings are appropriate on the server
 * Checks for Google API Installment, Database set up and Google API Credentials
 * @author beckmn
 * @version 1.0
 * @date 30.01.2017
 * **/
class Installer{
	/** some basic consts **/
	
	/** where to store the server configs **/
	const CONFIG_FILE='tasklist_config.mcb';
	/** path to 3rd party libs **/
	const LIB_DIR='libs';
	/** path to google api **/
	const G_API_PATH='google-api-php-client-2.1.1';
	/** url to google api**/
	const G_API_URL='https://github.com/google/google-api-php-client/releases/download/v2.1.1/google-api-php-client-2.1.1.zip';
	
	/** name of temp zip in case for downloading files **/
	const ZIP_NAME='temp.zip';
	
	
	/**member variables**/
	public $conf;
	
	protected static $_instance = null;
	
	public static function sharedInstaller()
	{
		if (null === self::$_instance)
		{
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	
	protected function __clone() {}
	protected function __construct() {
		//read config
		if(file_exists(Installer::CONFIG_FILE)){
			$this->conf=(array)json_decode(urldecode(file_get_contents(Installer::CONFIG_FILE)));
		}
		else{
			$this->conf=array();
		}
	}
	
	/**
	 * checks, if everything is properly set up.
	 * @return an array which defines things needed to set up.
	 * **/
	function checkInstallNeeds(){
		$needs=array();
		
		if(!isset($this->conf['uses_g_api'])){
			$needs[]='g_api_usage';
		}
		
		else if(isset($this->conf['uses_g_api'])){
			if($this->conf['uses_g_api']=="yes"){
				//check one: check if google
				if(!is_dir(Installer::LIB_DIR."/".Installer::G_API_PATH."/")){
					$needs[]="googleApi";
				}
				//check three: check if google credentials are stored
				if(!isset($this->conf['g_client_id'])||($this->conf['g_client_id'])=="")$needs[]='g_client_id';
				if(!isset($this->conf['g_client_secret'])||($this->conf['g_client_secret'])=="")$needs[]='g_client_secret';
				/**
				 @todo : is this really needed?
				 **/
				if(!isset($this->conf['g_redirect_uri'])||($this->conf['g_redirect_uri'])=="")$needs[]='g_redirect_uri';
				if(!isset($this->conf['g_simple_api_key'])||($this->conf['g_simple_api_key'])=="")$needs[]='g_simple_api_key';
			}
		}
		
		
		//check four: check if Database loginfiles 
		$set=0;
		if(!isset($this->conf['db_host'])||($this->conf['db_host'])=="")$needs[]='db_host';
		else $set++;
		if(!isset($this->conf['db_user'])||($this->conf['db_user'])=="")$needs[]='db_user';
		else $set++;
		if(!isset($this->conf['db_password'])||($this->conf['db_password'])=="")$needs[]='db_password';
		else $set++;
		if(!isset($this->conf['db_database'])||($this->conf['db_database'])=="")$needs[]='db_database';
		else $set++;
		
		//check five: check database connection credentials setup right
		if($set>=4){
			$database=new Database();
			if(strlen($database->error)>0){
				$needs[]='db_host';
				$needs[]='db_user';
				$needs[]='db_password';
				$needs[]='db_database';
			}
			else{
				//credentials are correct. check for creating or migrating the db
				/**
				 * TODO: Refactor this to a DB Migration class
				 * **/
				//check if table tl_settings exists
				$database->query("SELECT 1 FROM tl_settings LIMIT 1;");
				$database->execute();
				if(strlen($database->error)>0){
					$sql="CREATE TABLE `".Installer::sharedInstaller()->conf['db_database']."`.`tl_settings` ( ";
					$sql.="`key` VARCHAR(128) NOT NULL ,";
					$sql.=" `value` VARCHAR(128) NOT NULL,";
					$sql.="PRIMARY KEY (`key`)) ENGINE = InnoDB;";
					$database->query($sql);
					$database->execute();
					echo $database->error;
				}
				//check if table tl_users exists
				$database->query("SELECT 1 FROM tl_users LIMIT 1;");
				$database->execute();
				if(strlen($database->error)>0){
					$sql="CREATE TABLE `".Installer::sharedInstaller()->conf['db_database']."`.`tl_users` ( ";
					$sql.="`u_id` BIGINT NOT NULL AUTO_INCREMENT ,";
					$sql.=" `u_name` VARCHAR(256) NOT NULL ,";
					$sql.="`u_email` VARCHAR(256) NOT NULL , ";
					$sql.="`oauth_user_id` VARCHAR(256) NOT NULL ,";
					$sql.=" `oauth_user_page` VARCHAR(256) NOT NULL ,";
					$sql.=" `oauth_user_photo` VARCHAR(256) NOT NULL ,";
					$sql.=" `refresh_token` VARCHAR(256) NOT NULL , ";
          $sql.=" `last_login` BIGINT NOT NULL , ";
					$sql.="PRIMARY KEY (`u_id`)) ENGINE = InnoDB;";
					$database->query($sql);
					$database->execute();
					echo $database->error;
				}
				
				
				//check if table tl_tasks exists
				$database->query("SELECT 1 FROM tl_tasks LIMIT 1;");
				$database->execute();
				if(strlen($database->error)>0){
					$sql="CREATE TABLE `".Installer::sharedInstaller()->conf['db_database']."`.`tl_tasks` ( ";
					$sql.="`t_id` BIGINT NOT NULL AUTO_INCREMENT ,";
					$sql.="`t_desc` TEXT NOT NULL ,";
					$sql.="`urgent` TINYINT NOT NULL DEFAULT '0' ,";
					$sql.="`important` TINYINT NOT NULL DEFAULT '0' ,";
					$sql.="`done` TINYINT NOT NULL DEFAULT '0' ,";
					$sql.="`expiration` INT NULL DEFAULT '0' ,";
					$sql.="`repeat_interval` INT NOT NULL DEFAULT '0' ,";
					$sql.="`repeat_since` INT NOT NULL DEFAULT '0' ,";
					$sql.="`repeat_until` INT NOT NULL DEFAULT '0' ,";
					$sql.="PRIMARY KEY (`t_id`)) ENGINE = InnoDB";
					$database->query($sql);
					$database->execute();
					echo $database->error;	
				}
				
				//check if table tl_tasks_history_done exists
				$database->query("SELECT 1 FROM tl_tasks_history LIMIT 1;");
				$database->execute();
				if(strlen($database->error)>0){
					$sql="CREATE TABLE `".Installer::sharedInstaller()->conf['db_database']."`.`tl_tasks_history` ( ";
					$sql.="`t_id` INT NOT NULL , `done` INT NOT NULL ) ENGINE = InnoDB;";
					$database->query($sql);
					$database->execute();
					echo $database->error;
				}
				
				//check version of db
				$database->query("SELECT `key`,`value` FROM tl_settings WHERE `key`='version'");
				$row=$database->single();
				
				//set to version 1 if not set yet
				if(!is_array($row)){
					$database->query("INSERT INTO tl_settings (`key`,`value`) VALUES ('version','1')");
					$database->execute();
					echo "hier her!";
					echo $database->error;
				}
				
				
			}
		
		}
	
		//check six: check google credentials are right
	
		return $needs;
	}
	
	/**
	 * saves the config array to the config file
	 * **/
	function saveConfig(){
		file_put_contents(Installer::CONFIG_FILE,json_encode($this->conf));
	}
	/***
	 * download all Libs required to run the scripts.
	 * @return an array with errors or 0, if everything went fine
	 ***/
	function downloadLibs(){
		$output=array();
		if(!is_dir(Installer::LIB_DIR."/".Installer::G_API_PATH."/")){
			$url = Installer::G_API_URL;
			$zipFile = Installer::ZIP_NAME;
			$zipResource = fopen($zipFile, "w");
			// Get The Zip File From Server
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_FILE, $zipResource);
			$page = curl_exec($ch);
			if(!$page) {
				$output[]="- ".curl_error($ch);
			}
			curl_close($ch);
	
			/* Open the Zip file */
			$zip = new ZipArchive;
			$extractPath = "libs";
			if($zip->open($zipFile) != "true"){
				$output[]="- Unable to open the Zip File";
			}
			/* Extract Zip File */
			$zip->extractTo($extractPath);
			$zip->close();
			unlink($zipFile);
		}
		if(count($output)==0)return 0;
		return $output;
	}
}

