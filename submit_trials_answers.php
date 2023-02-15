<?php
session_start();
header('content-type:application/json;charset=utf-8');
//header("Content-Type: text/plain");

require('../database/DBConnect.php');
$dbManager = new DBConnect();
try
{
   $db = $dbManager->DBConnection();
}
catch(PDOException $e)
{
    echo "Connection to database FAILED : " . $e->getMessage();
}

if( isset($_POST['data']) && isset($_SESSION['idEleve']) && isset($_SESSION['group']) )
{
	$resultArray = json_decode($_POST['data'], true);
	$idUser = (int) $_SESSION['idEleve'];
	$nbBlocks = sizeof($resultArray['blocks']);
	$strSql = '';
	$arrayMerge = [];

	for( $numBlock=0; $numBlock<$nbBlocks; $numBlock++ )
	{
		$nbStims = sizeof($resultArray['blocks'][$numBlock]['stims']);
		// For each stim in the current block
		for ( $numStim = 0; $numStim < $nbStims; $numStim++ ) 
		{
		    $rt = (float) htmlspecialchars($resultArray['rt'][$numBlock][$numStim]);
		    $strSql .= 'INSERT INTO preexpresultsraw(idEleve, numBlock, numTrial, item, isLeft, accuracy, rt, exclude) VALUES(';
			$exclude = (int) ($rt > 10000 || $rt < 400);

	    	$arrayMerge[] = $idUser;
			$arrayMerge[] = ($numBlock+1);
			$arrayMerge[] = ($numStim+1);
			$arrayMerge[] = htmlspecialchars($resultArray['blocks'][$numBlock]['stims'][$numStim]['stim']);
			$arrayMerge[] = (int) htmlspecialchars($resultArray['blocks'][$numBlock]['stims'][$numStim]['isLeft']);
			$arrayMerge[] = (int) htmlspecialchars($resultArray['answers'][$numBlock][$numStim]);
			$arrayMerge[] = $rt;
			$arrayMerge[] = $exclude;

			$strSql .= '?,?,?,?,?,?,?,?);';
	    }
	}

    // Push stim/trial into the raw database (resultsRaw)
    $query = $db->prepare($strSql);
	$query->execute($arrayMerge) or exit(print_r($db->errorInfo()));

	echo json_encode(array("success" => true));
}
else
	echo json_encode(array("success" => false));