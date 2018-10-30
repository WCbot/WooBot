<?php
if ($_POST['text'] == "test") {
	$response_array = array(
	"username" => "WooBot",
	"channel" => $_POST['channel_id'],
	"response_type" => "in_channel",
	"text" => "Hello, WooBot here, I'm working!",
	"mrkdwn" => false,
);
} else {
	$response_array = array(
	"username" => "WooBot",
	"channel" => $_POST['channel_id'],
	"response_type" => "ephemeral",
	"text" => "Sorry, command not recognised",
	"mrkdwn" => false,
);
}
 
$response_json = json_encode($response_array);

header('content-type: application/json');
echo $response_json;
?>
