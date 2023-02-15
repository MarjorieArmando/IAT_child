<?php

class DBConnect
{
	private $hname = "localhost";
	private $dname = "iat";

	// Mettre le login de la base de données entre les guillements
	private $usern = "";
	// Mettre le mdp de la base de données entre les guillemets
	private $pword = "";

	private $opt = array(
	    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
	);

	public function DBConnection()
	{
		$dsn = "mysql:host=" . $this->hname . ";dbname=" . $this->dname . ";charset=utf8";
	    $db = new PDO($dsn, $this->usern, $this->pword, $this->opt);
	    return $db;
	}
}