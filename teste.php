<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once 'Group.php';

$xml = file_get_contents('Como_esta_saindo.xml');
$out = Group::addXmlEvents([$xml]);


header('Content-Type: application/xml');
echo $out;


