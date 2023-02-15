<?php
session_start();
//header('content-type:application/json;charset=utf-8');
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

function getAmplitude($d_score)
{
	if($d_score > 0.65 || $d_score < -0.65)
		return ", ce qui suggère une forte association automatique ";
	else if($d_score > 0.35 || $d_score < -0.35)
		return ", ce qui suggère une association automatique moyenne ";
	else if($d_score > 0.15 || $d_score < -0.15)
		return ", ce qui suggère une légère association automatique ";
	return ", ce qui suggère peu à pas d'association automatique entre genre et discipline.";
}

function getOrientation($d_score)
{
	if($d_score>0.15)
		return "entre hommes-sciences et femmes-lettres.";
	else if($d_score<-0.15)
		return "entre femmes-sciences et hommes-lettres.";
	return "";
}

function standardDeviation($blockA, $blockB)
{
	// Pooled
	$pooledRT = array_merge($blockA, $blockB);
	// Mean
	$sizeArr = count($pooledRT);
	if( $sizeArr > 0 )
  		$mean = array_sum($pooledRT)/(float)$sizeArr;
  	
  	$sumDeviation = 0;
  	for($i=0; $i<$sizeArr; $i++)
  	{
  		// Subtract the mean from each rt + Square each deviation
  		$sumDeviation += (($pooledRT[$i] - $mean) * ($pooledRT[$i] - $mean));
  	}
  	if( $sizeArr-1 > 0 )
  		return sqrt($sumDeviation/(float)($sizeArr-1));
  	else
  		return 0;
}

function isBlockCompatible($numBlock)
{
    if($_SESSION['group'] == 0)
    {
    	if( $numBlock == 2 || $numBlock == 3 )
    		return 1;
    	else if( $numBlock == 5 || $numBlock == 6 )
    		return 0;
    }
    if($_SESSION['group'] == 1)
    {
    	if( $numBlock == 2 || $numBlock == 3 )
    		return 0;
    	else if( $numBlock == 5 || $numBlock == 6 )
    		return 1;
    }
    return 2;
}


if( isset($_POST['data']) && isset($_SESSION['group']) && isset($_SESSION['idEleve']) )
{
	$resultArray = json_decode($_POST['data'], true);
	$idEleve = (int) $_SESSION['idEleve'];

	$tempsMax = (float) htmlspecialchars($resultArray['tempsMax']);
	$noScore = false;
	$nbBlocks = sizeof($resultArray['blocks']);
	$nb300 = 0;
	$nbCorrect = 0;
	$nbTotalItems = 0;
	$meanBlocks = [];
	$numBlockExp = [2,3,5,6];
	$blockRTcorrect = [];

	for( $numBlock=0; $numBlock<$nbBlocks; $numBlock++ )
	{
		$nbItemLeft = 0;
		$nbItemRight = 0;
		$nbGoodTrials = 0;
		$mean = 0;
		$catLeftRT = 0;
		$catRightRT = 0;
		$nbStims = sizeof($resultArray['blocks'][$numBlock]['stims']);
		$rtCorrect = [];
	    $areCatCompatible = isBlockCompatible($numBlock);
	    $catLeft = strip_tags($resultArray['blocks'][$numBlock]['left']);
	    $catRight = strip_tags($resultArray['blocks'][$numBlock]['right']);

		// For each stim in the current block
		for ( $numStim = 0; $numStim < $nbStims; $numStim++ ) 
		{
		    $acc = (int) htmlspecialchars($resultArray['answers'][$numBlock][$numStim]);
		    $stim = htmlspecialchars($resultArray['blocks'][$numBlock]['stims'][$numStim]['stim']);
		    $isLeft = (int) htmlspecialchars($resultArray['blocks'][$numBlock]['stims'][$numStim]['isLeft']);
		    $rt = (float) htmlspecialchars($resultArray['rt'][$numBlock][$numStim]);
		    $exclude = 1;

		    $nbCorrect += $acc;
	    	if( $rt < 300 )
	    		$nb300++;
			if( $rt <= 10000 && $rt >= 400 )
			{
				$nbGoodTrials++;
				$exclude = 0;
				$mean += $rt;

				if( in_array($numBlock, $numBlockExp) )
					$rtCorrect[] = $rt;
				
				if($isLeft)
				{
					$catLeftRT += $rt;
					$nbItemLeft++;
				}
				else
				{
					$catRightRT += $rt;
					$nbItemRight++;
				}
			}
	    	$nbTotalItems++;
	    }

		if($nbItemLeft > 0)
			$catLeftRT = $catLeftRT / (float) $nbItemLeft;
		if($nbItemRight > 0)
			$catRightRT = $catRightRT / (float) $nbItemRight;

		// Mettre dans summaryBlocks
		
	    $req = $db->prepare('INSERT INTO preexpsummaryblocks(idEleve, numBlock, catLeft, catRight, areCatCompatible, meanCatLeftRT, meanCatRightRT) 
			VALUES(:idEleve, :numBlock, :catLeft, :catRight, :areCatCompatible, :meanCatLeftRT, :meanCatRightRT);');
		$req->execute(array(
			'idEleve' => $idEleve,
			'numBlock' => ($numBlock+1),
			'catLeft' => $catLeft,
			'catRight' => $catRight,
			'areCatCompatible' => $areCatCompatible,
			'meanCatLeftRT' => $catLeftRT,
			'meanCatRightRT' => $catRightRT
		)) or exit(print_r($db->errorInfo()));
		$req->closeCursor();
		
		
	    	
		// Calcul mean for blocks 3, 4, 6, 7 
		// (Remember indexes in array starts at 0, so for us it's block 2, 3, 5, 6)
		if( in_array($numBlock, $numBlockExp) )
		{
			$blockRTcorrect[] = $rtCorrect;
			if($nbGoodTrials>0)
				$meanBlocks[] = (float)$mean/(float)$nbGoodTrials;
			else
				$meanBlocks[] = 0;
		}
	}
	
	$propRT300 = ((float)$nb300/(float)$nbTotalItems)*100;
	$percentCorrect = ((float)$nbCorrect/(float)$nbTotalItems)*100;



	// No score given if the user is exclude
	if( $propRT300 > 10 )
		$noScore = true;

	// Calcul std for B3-B6 B4-B7
	$pooled_std_b3b6 = standardDeviation($blockRTcorrect[0], $blockRTcorrect[2]);
	$pooled_std_b4b7 = standardDeviation($blockRTcorrect[1], $blockRTcorrect[3]);

	if( $_SESSION['group']==0 )
	{
		$diff_b6b3 = 0;
		if($pooled_std_b3b6 != 0)
			$diff_b6b3 = ($meanBlocks[2] - $meanBlocks[0]) / $pooled_std_b3b6;
		$diff_b4b7 = 0;
		if($pooled_std_b4b7)
			$diff_b4b7 = ($meanBlocks[3] - $meanBlocks[1]) / $pooled_std_b4b7;
	}
	else
	{
		$diff_b6b3 = 0;
		if($pooled_std_b3b6!=0)
			$diff_b6b3 = ($meanBlocks[0] - $meanBlocks[2]) / $pooled_std_b3b6;
		$diff_b4b7 = 0;
		if($pooled_std_b4b7)
			$diff_b4b7 = ($meanBlocks[1] - $meanBlocks[3]) / $pooled_std_b4b7;		
	}
	$d_score = ($diff_b6b3 + $diff_b4b7) / 2;

    $req = $db->prepare(
    	'INSERT INTO preexpresultssummary(
		idEleve,
		ic_c,
		tempsMax, 
		entireExp, 
		exclude, 
		meanB3, 
		meanB4,
		meanB6,
		meanB7,
		stdB3B6,
		stdB4B7,
		da,
		db,
		dScore,
		percentCorrect,
		propRT300) 
		VALUES (
		:idEleve,
		:ic_c,
		:tempsMax,
		:entireExp,
		:exclude,
		:meanB3, 
		:meanB4,
		:meanB6,
		:meanB7,
		:stdB3B6,
		:stdB4B7,
		:da,
		:db,
		:dScore,
		:percentCorrect,
		:propRT300
		);');
	$req->execute(array(
		'idEleve' => $idEleve,
		'ic_c' => (int)$_SESSION['group'],
		'tempsMax' => $tempsMax,
		'entireExp' => 1,
		'exclude' => (int) ($propRT300 > 10),
		'meanB3' => $meanBlocks[0],
		'meanB4' => $meanBlocks[1],
		'meanB6' => $meanBlocks[2],
		'meanB7' => $meanBlocks[3],
		'stdB3B6' => $pooled_std_b3b6,
		'stdB4B7' => $pooled_std_b4b7,
		'da' => $diff_b6b3,
		'db' => $diff_b4b7,
		'dScore' => $d_score,
		'percentCorrect' => $percentCorrect,
		'propRT300' => $propRT300
	)) or exit(print_r($db->errorInfo()));
	$req->closeCursor();
	

	// Faire une redirection : aller sur la bonne leçon
	/*
	echo json_encode(array(
		"url" => $_SESSION['url']
	));
	*/
}
/*
else
{
	
	echo json_encode(array(
		"url" => $_SESSION['url']
	));	
}
*/