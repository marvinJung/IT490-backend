#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doLogin($ID,$pass)
{
    //sending the data from back-end to the database with new queue!  
    $database = new rabbitMQClient("databaseRabbitMQ.ini","testServer");
    
    $credential = array();
    $credential['type'] = "Login";
    $credential['ID'] = $ID;
    $credential['pass'] = $pass;
    $database_response = $database->send_request($credential);
    echo "Back-End VM received response from database VM".PHP_EOL;

    return $database_response;
}

function doRegister($ID,$pass,$firstName,$lastName,$email)
{
	//sending the data from back-end to the database with new queue!
	$database = new rabbitMQClient("databaseRabbitMQ.ini","testServer");

	$credential = array();
	$credential['type'] = "Register";
	$credential['ID'] = $ID;
	$credential['pass'] = $pass;
	$credential['firstName'] = $firstName;
	$credential['lastName'] = $lastName;
	$credential['email'] = $email;
	$database_response = $database->send_request($credential);
	echo "Back-End VM received response from database VM".PHP_EOL;
		
	return $database_response;
}

function requestProcessor($request)
{
  echo "Back-End VM received request".PHP_EOL;
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  if($request['type']=="Login")
  {
      print_r($request);	
      return doLogin($request['ID'],$request['pass']);
  }
  if($request['type']=="Register")
  {
      print_r($request);
      return doRegister($request['ID'],$request['pass'],$request['firstName'],$request['lastName'],$request['email']);
  }
  if($request['type']=="validate_session")
  {
      return doValidate($request['sessionId']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "BackEndRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

