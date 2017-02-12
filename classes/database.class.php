<?php
/**
Database wrapper for PDO SQL operations
@author Merlin Becker
@version 1.0
@date 02.02.2017

parts taken from :
@see http://culttt.com/2012/10/01/roll-your-own-pdo-php-class/

**/

class Database{
	/**db credentials**/
	private $host;
	private $user;
	private $pass;
	private $dbname;
	
	/**db connection variables**/
	private $dbh;
	public $error;
	
	/**db query variables**/
	private $stmt;
	
	public function __construct(){
		$this->host=Installer::sharedInstaller()->conf['db_host'];
		$this->user=Installer::sharedInstaller()->conf['db_user'];
		$this->pass=Installer::sharedInstaller()->conf['db_password'];
		$this->dbname=Installer::sharedInstaller()->conf['db_database'];	
		$this->setup();
	}
	
	private function setup(){
		/**
		 * @todo: use dependency injection here
		 * **/
		$dsn='mysql:host='.$this->host.';dbname='.$this->dbname;
		$options=array(
				PDO::ATTR_PERSISTENT=>true,
				PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
		);
		
		try{
			$this->dbh=new PDO($dsn,$this->user,$this->pass,$options);
		}catch(PDOException $e){
			$this->error=$e->getMessage();
		}
	}
	
	public function query($query){
		$this->stmt=$this->dbh->prepare($query);		
	}
	public function bind($param,$value,$type=null){
		if(is_null($type)){
			switch(true){
				case is_int($value):
					$type=PDO::PARAM_INT;
					break;
				case is_bool($value):
					$type=PDO::PARAM_BOOL;
					break;
				case is_null($value):
					$type=PDO::PARAM_NULL;
					break;
				default:
					$type=PDO::PARAM_STR;
			}
		}
		$this->stmt->bindValue($param,$value,$type);
	}
	public function execute(){
		$this->error="";
		try{
			return $this->stmt->execute();
		}
		catch(PDOException $e){
			$this->error=$e->getMessage();
		}
	}
	public function resultset(){
		$this->execute();
		return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	public function single(){
		$this->execute();
		return $this->stmt->fetch(PDO::FETCH_ASSOC);
	}
	public function rowCount(){
		return $this->stmt->rowCount();
	}
	public function lastInsertId(){
		return $this->dbd->lastInsertId();
	}
	public function beginTransaction(){
		return $this->dbd->beginTransaction();
	}
	public function endTransaction(){
		return $this->dbh->commit();
	}
	public function cancelTransaction(){
		return $this->dbh->rollBack();
	}
	public function debugDumpParams(){
		return $this->stmt->debugDumpParams();
	}
}
