<?php
/**
 * Created by PhpStorm.
 * User: jadorable
 * Date: 2/26/2015
 * Time: 2:39 PM
 */
require 'vendor/autoload.php';

class User {

    public $dburl = "";
    public $dbhost = "";
    public $dbuser = "";
    public $dbpass = "";
    public $dbname = "";    
    public $db;
    public $error = "";

    public function __construct(){
    	$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
        $this->dbhost = $url["host"];
        $this->dbuser = $url["user"];
        $this->dbpass = $url["pass"];
        $this->dbname = substr($url["path"], 1);

        $this->dburl = "mysql:host=" . $this->dbhost . ";dbname=" . $this->dbname . ";charset=utf8";
		        
        $this->connect();
    }

    public function connect(){
        try {
            $this->db = new PDO($this->dburl, $this->dbuser, $this->dbpass);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        catch(PDOException $ex){
            echo "An Error occurred could not connect to database!";
            echo $ex->getMessage();
        }
    }

    public function __destruct(){
        $this->db = null;
    }


    public function register($email, $password, $logindatetime, $ipaddress, $loginattempt){
        try {
            $stmt = $this->db->prepare("INSERT INTO users(email, password, logindatetime, ipaddress, loginattempt) ".
                                       "VALUES(?, PASSWORD(?), ?, ?, ?)");
            $stmt->execute(array($email, $password));
        }
        catch(PDOException $ex){
            echo "An Error occurred error inserting record!";
            echo $ex->getMessage();
        }
    }
    
    public function update($email, $password, $logindatetime, $ipaddress, $loginattempt){
    	try {
            $stmt = $this->db->prepare("UPDATE users SET password=PASSWORD(?), logindatetime=?, ipaddress=?, loginattempt=? ".
                                       "WHERE email=?");
            $stmt->execute(array($password, $logindatetime, $ipaddress, $loginattempt, $email));
        }
        catch(PDOException $ex){
            echo "An Error occurred error inserting record!";
            echo $ex->getMessage();
        }
    	
    }
	
	public function exists($email){
		$exists = FALSE;
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email=?");
            $stmt->execute(array($email));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$row_count = $stmt->rowCount();
			var_dump($row_count);
            if($row_count > 0){
            	$exists = TRUE;
            }
        }
        catch(PDOException $ex){
            echo "An Error occurred error inserting record!";
            echo $ex->getMessage();
        }
        return $exists;
    }

    public function login($email, $password){
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email=? AND password=PASSWORD(?)");
            $stmt->execute(array($email, $password));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rows;
        }
        catch(PDOException $ex){
            echo "An Error occurred error inserting record!";
            echo $ex->getMessage();
        }
    }	
	
	public function getlogininfo($email){
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email=?");
            $stmt->execute(array($email));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rows;
        }
        catch(PDOException $ex){
            echo "An Error occurred error inserting record!";
            echo $ex->getMessage();
        }
    }
	    
    public function validate($email, $password){
        $valid = TRUE;
        //Validate input email address        
        if(!(filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $email))){            
            $this->error = "Invalid email address.";
            $valid = FALSE;
        }        
		
		//Validate input password
		/*
	    Explaining $\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$
	    $ = beginning of string
	    \S* = any set of characters
	    (?=\S{8,}) = of at least length 8
	    (?=\S*[a-z]) = containing at least one lowercase letter
	    (?=\S*[A-Z]) = and at least one uppercase letter
	    (?=\S*[\d]) = and at least one number
	    (?=\S*[\W]) = and at least a special character (non-word characters)
	    $ = end of the string	
	    */
		if (!preg_match_all('$\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$', $password)){
			if($this->error != ""){
				$this->error .= "<br />";
			}
			$this->error .= "Password does not meet the requirements.";
			$valid = FALSE; 
		}
            
        return $valid;
    }

	public function sendnotification(){
		$options = array(
		    'turn_off_ssl_verification' => false,
		    'protocol' => 'smtp',
		    'host' => 'api.sendgrid.com',
		    'endpoint' => '/api/mail.send.json',
		    'port' => 587,
		    'url' => null,
		);
		$sendgrid = new SendGrid('app34112757@heroku.com', 'ri9aacvv', $options);
				
		$message = new SendGrid\Email();
		$message->addTo('foo@bar.com')->
		          setFrom('me@bar.com')->
		          setSubject('Subject goes here')->
		          setText('Hello World!')->
		          setHtml('<strong>Hello World!</strong>');
		$response = $sendgrid->send($message);
	}
}