<?php
session_start();
header('content-type:application/json;charset=utf-8');

class Stim
{
  // attibutes are public to use json_encode()
  public $cat;
  public $strStims;
  public $color;

  public function __construct($cat, $color, $strStims)
  {
    $this->cat = $cat;
    $this->color = $color;
    $this->strStims = $strStims;
    //$this->items = array();
  }
  public function addItem($item)
  {
    $this->items[] = $item;
  }
}

function readExpFile($filename)
{
  $json = file_get_contents($filename);
  return $json;
}

function getCatAndStims($data)
{
  $catAndStims = [];
  $nbCat = count($data["cat_and_stims"]);

  for($i=0; $i < $nbCat; $i++)
  {
    $strStims = "";
    $nbItems = count($data["cat_and_stims"]["cat_".($i+1)]["stims"]);
    for($j=0; $j < $nbItems; $j++)
    {
      $strStims .= $data["cat_and_stims"]["cat_".($i+1)]["stims"][$j][0];
      if( $j != $nbItems-1 )
        $strStims .= ', ';
    }
    $catAndStims[] = new Stim($data["cat_and_stims"]["cat_".($i+1)]["title"], $data["cat_and_stims"]["cat_".($i+1)]["color"], $strStims);
  }

  return $catAndStims;
}

$data = json_decode(readExpFile("stim/stim_sound.json"), true);
$catAndStims = getCatAndStims($data);

echo json_encode(array(
  $catAndStims
));