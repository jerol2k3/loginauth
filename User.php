<?php
/**
 * Created by PhpStorm.
 * User: jadorable
 * Date: 2/26/2015
 * Time: 2:39 PM
 */

class User {

    public $dburl = "";
    public $dbhost = "";
    public $dbuser = "";
    public $dbpass = "";
    public $dbname = "";
    public $db = "";

    public function __construct(){
        $url = parse_url(getenv("CLEARDB_DATABASE_URL"));
        $this->dbhost = $url["host"];
        $this->dbuser = $url["user"];
        $this->dbpass = $url["pass"];
        $this->dbname = substr($url["path"], 1);

        var_dump($this->dbhost);
        var_dump($this->dbuser);
        var_dump($this->dbpass);
        var_dump($this->dbname);

        $this->dburl = "mysql:host=" . $this->dbhost . ";dbname=" . $this->dbname . ";charset=utf8";
        var_dump($this->dburl);
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


    public function register($email, $password){
        try {
            $stmt = $this->db->prepare("INSERT INTO users(email, password) VALUES(?, PASSWORD(?))");
            $stmt->execute(array($email, $password));
        }
        catch(PDOException $ex){
            echo "An Error occurred error inserting record!";
            echo $ex->getMessage();
        }
    }

    public function login($email, $password){
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email=? AND password=PASSWORD(?)");
            $stmt->execute(array($email, $password));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            var_dump($rows);
        }
        catch(PDOException $ex){
            echo "An Error occurred error inserting record!";
            echo $ex->getMessage();
        }
    }
}