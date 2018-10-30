<?php
if ($_POST['text'] == "test")
	$response_array = array(
	"username" => "WooBot",
	"channel" => $_POST['channel_id'],
	"response_type" => "in_channel",
	"text" => "Hello, WooBot here, I'm working!",
	"mrkdwn" => false,
	"icon_url" => "http://www.gfxmag.com/wp-content/uploads/2016/07/woocommerce-vector-logo.png",
);

$response_json = json_encode($response_array);

header('content-type: application/json');
echo $response_json;
?>
