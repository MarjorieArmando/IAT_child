<?php
session_start();
header('content-type:application/json;charset=utf-8');

class Stim
{
  // attibutes are public to use json_encode()
  public $stim;
  public $isLeft;
  public $color;
  public $sound;

  public function __construct($stim, $isLeft, $color, $sound)
  {
    $this->stim = $stim;
    $this->isLeft = $isLeft;
    $this->color = $color;
    $this->sound = $sound;
  }
}

function readExpFile($filename)
{
  $json = file_get_contents($filename);
  return $json;
}

function fillArrayIndexes($sizeCat)
{
  $randIndexes = [];
  for($i=0; $i < $sizeCat; $i++)
    $randIndexes[$i] = $i;
  return $randIndexes;
}

function getRandomStim(&$block, $data, $nbSample, $cat, $sizeCat, $isCatALeft)
{
  $randIndexes = fillArrayIndexes($sizeCat);

  for($i=0; $i < $nbSample; $i++)
  {
    if( empty($randIndexes) )
      $randIndexes = fillArrayIndexes($sizeCat);
    
    $random = array_rand($randIndexes);
    unset($randIndexes[$random]);

    $block[] = new Stim($data["cat_and_stims"][$cat]["stims"][$random][0], $isCatALeft, $data["cat_and_stims"][$cat]["color"], $data["cat_and_stims"][$cat]["stims"][$random][1]);
  }
}

// Compatible : Sciences/Male | Liberal Arts/Female
// Incompatible : Sciences/Female | Liberal Arts/Male
function getBlockExp($data, $nbSample, $catA1, $catA2, $catB1, $catB2, $choseSide, $randomSide)
{
  $block = [];
  $isCatALeft = (bool) $choseSide;
  if( $randomSide )
    $isCatALeft = (bool) random_int(0, 1);

  $htmlCatA = "<span style='color:".$data["cat_and_stims"][$catA1]["color"].";'>".
  	$data["cat_and_stims"][$catA1]["title"].
  	"</span><br/> ou <br/><span style='color:".$data["cat_and_stims"][$catA2]["color"].";'>".
  	$data["cat_and_stims"][$catA2]["title"]."</span>";

  $htmlCatB = "<span style='color:".$data["cat_and_stims"][$catB1]["color"].";'>".
  	$data["cat_and_stims"][$catB1]["title"].
  	"</span><br/> ou <br/><span style='color:".$data["cat_and_stims"][$catB2]["color"].";'>".
  	$data["cat_and_stims"][$catB2]["title"]."</span>";

  $left = $htmlCatB;
  $right = $htmlCatA;  
  if( $isCatALeft )
  {
    $left = $htmlCatA;
    $right = $htmlCatB;
  }

  $sizeCatA1 = count($data["cat_and_stims"][$catA1]["stims"]);
  $sizeCatA2 = count($data["cat_and_stims"][$catA2]["stims"]);
  $sizeCatB1 = count($data["cat_and_stims"][$catB1]["stims"]);
  $sizeCatB2 = count($data["cat_and_stims"][$catB2]["stims"]);

  // Take nbSample/4 in catA1
  getRandomStim($block, $data, $nbSample/4, $catA1, $sizeCatA1, $isCatALeft);
  // Take nbSample/4 in catA2
  getRandomStim($block, $data, $nbSample/4, $catA2, $sizeCatA2, $isCatALeft);
  // Take nbSample/4 in catB1
  getRandomStim($block, $data, $nbSample/4, $catB1, $sizeCatB1, !$isCatALeft);
  // Take nbSample/4 in catB2
  getRandomStim($block, $data, $nbSample/4, $catB2, $sizeCatB2, !$isCatALeft);
  // Random
  //shuffle($block);

  $randNum = -1;
  $randItem = "";
  $prevItem = "";
  $shuffleBlock = [];
  for($sizeBlock = count($block); $sizeBlock > 0; $sizeBlock--)
  {
  	do
  	{
  		$randNum = rand(0, $sizeBlock-1);
  		$randItem = $block[$randNum];
  	}while($randItem == $prevItem && $sizeBlock != 1);
  	$prevItem = $randItem;
  	unset($block[$randNum]);
  	$block = array_values($block);
  	$shuffleBlock[] = $randItem;
  }
  return [
    "isCatALeft" => $isCatALeft,
    "left" => $left,
    "right" => $right,
    "stims" => $shuffleBlock
  ];
}

function getBlockPractice($data, $nbSample, $catA, $catB, $choseSide, $randomSide)
{
  $block = [];
  $isCatALeft = (bool) $choseSide;
  if( $randomSide )
    $isCatALeft = (bool) random_int(0, 1);

  $htmlCatA = "<span style=\"color:".$data["cat_and_stims"][$catA]["color"].";\">".$data["cat_and_stims"][$catA]["title"]."</span>";
  $htmlCatB = "<span style=\"color:".$data["cat_and_stims"][$catB]["color"].";\">".$data["cat_and_stims"][$catB]["title"]."</span>";

  $left = $htmlCatB;
  $right = $htmlCatA;
  if( $isCatALeft )
  {
    $left = $htmlCatA;
    $right = $htmlCatB;
  }
  $sizeCatA = count($data["cat_and_stims"][$catA]["stims"]);
  $sizeCatB = count($data["cat_and_stims"][$catB]["stims"]);

  // Take nbSample/2 in catA
  getRandomStim($block, $data, $nbSample/2, $catA, $sizeCatA, $isCatALeft);
  // Take nbSample/2 in catB
  getRandomStim($block, $data, $nbSample/2, $catB, $sizeCatB, !$isCatALeft);
  // Random
  //shuffle($block);
  $randNum = -1;
  $randItem = "";
  $prevItem = "";
  $shuffleBlock = [];
  for($sizeBlock = count($block); $sizeBlock > 0; $sizeBlock--)
  {
  	do
  	{
  		$randNum = rand(0, $sizeBlock-1);
  		$randItem = $block[$randNum];
  	}while($randItem == $prevItem && $sizeBlock != 1);
  	$prevItem = $randItem;
  	unset($block[$randNum]);
  	$block = array_values($block);
  	$shuffleBlock[] = $randItem;
  }
  return [
    "isCatALeft" => $isCatALeft,
    "left" => $left,
    "right" => $right,
    "stims" => $shuffleBlock
  ];
}

$data = json_decode(readExpFile("stim/stim_sound.json"), true);

$b1 = getBlockPractice($data, $data["nb_sample_b1"], $data["b1_cat"][0], $data["b1_cat"][1], false, true);
$b2 = getBlockPractice($data, $data["nb_sample_b2"], $data["b2_cat"][0], $data["b2_cat"][1], false, true);
// c-ic
if($_SESSION['group'] == 0)
{
  $b3 = getBlockExp($data, $data["nb_sample_b3"], $data["cat_compatible_A"][0], $data["cat_compatible_A"][1],
  	$data["cat_compatible_B"][0], $data["cat_compatible_B"][1], false, true); 
  $b4 = getBlockExp($data, $data["nb_sample_b4"], $data["cat_compatible_A"][0], $data["cat_compatible_A"][1],
  	$data["cat_compatible_B"][0], $data["cat_compatible_B"][1], $b3["isCatALeft"], false);
}
// ic-c
else
{
  $b3 = getBlockExp($data, $data["nb_sample_b3"], $data["cat_incompatible_A"][0], $data["cat_incompatible_A"][1],
  	$data["cat_incompatible_B"][0], $data["cat_incompatible_B"][1], false, true); 
  $b4 = getBlockExp($data, $data["nb_sample_b4"], $data["cat_incompatible_A"][0], $data["cat_incompatible_A"][1],
  	$data["cat_incompatible_B"][0], $data["cat_incompatible_B"][1], $b3["isCatALeft"], false);  
}
$b5 = getBlockPractice($data, $data["nb_sample_b5"], $data["b5_cat"][0], $data["b5_cat"][1], !$b1["isCatALeft"], false);
// c-ic
if($_SESSION['group'] == 0)
{
  $b6 = getBlockExp($data, $data["nb_sample_b6"], $data["cat_incompatible_A"][0], $data["cat_incompatible_A"][1],
  	$data["cat_incompatible_B"][0], $data["cat_incompatible_B"][1], false, true); 
  $b7 = getBlockExp($data, $data["nb_sample_b7"], $data["cat_incompatible_A"][0], $data["cat_incompatible_A"][1],
  	$data["cat_incompatible_B"][0], $data["cat_incompatible_B"][1], $b6["isCatALeft"], false);
}
// ic-c
else
{
  $b6 = getBlockExp($data, $data["nb_sample_b6"], $data["cat_compatible_A"][0], $data["cat_compatible_A"][1],
  	$data["cat_compatible_B"][0], $data["cat_compatible_B"][1], false, true); 
  $b7 = getBlockExp($data, $data["nb_sample_b7"], $data["cat_compatible_A"][0], $data["cat_compatible_A"][1],
  	$data["cat_compatible_B"][0], $data["cat_compatible_B"][1], $b6["isCatALeft"], false);
}

echo json_encode(array(
  $b1,
  $b2,
  $b3,
  $b4,
  $b5,
  $b6,
  $b7
));