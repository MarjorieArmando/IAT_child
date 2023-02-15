<?php
session_start();
header('content-type:application/json;charset=utf-8');

function readExpFile($filename)
{
  $json = file_get_contents($filename);
  return $json;
}

function getInstructionsBlock($data)
{
  $instructions = [];
  $size = 0;
  $maxBlocks = $data["nb_blocks_max"];
  $nbInstructions = count($data["instructions_blocks"]);

  if( $maxBlocks != $nbInstructions )
    $size = max($maxBlocks, $nbInstructions);

  for($i=0; $i < $nbInstructions; $i++)
    $instructions[] = $data["instructions_blocks"]["b".($i+1)];
  for($i=$nbInstructions; $i < $size; $i++)
    $instructions[] = "";

  return $instructions;
}
$instructionsBlock = [];
$data = json_decode(readExpFile("stim/stim_sound.json"), true);
$instructionsBlock = getInstructionsBlock($data);

echo json_encode(array(
  "nbBlockMax" => $data["nb_blocks_max"],
  "instructionsWelcomeA" => $data["instructions_welcome_A"],
  "instructionsWelcomeB" => $data["instructions_welcome_B"],
  "instructionsBlock" => $instructionsBlock,
  "leftKey" => $data["left_key"],
  "rightKey" => $data["right_key"],
  "continueKey" => $data["continue_key"]
));