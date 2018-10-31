<?php
$signingsecret = "d359026511347aa1caffacb200c332a8";
$basestring = "v0" . ":" . $_SERVER['HTTP_X_SLACK_REQUEST_TIMESTAMP'] . ":" . file_get_contents('php://input');
$mysignature = "v0=" . hash_hmac("sha256", $basestring, $signingsecret);
if ($mysignature == $_SERVER['HTTP_X_SLACK_SIGNATURE']) {
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
} else {
	header('content-type: text/plain');
	echo "Sorry, it doesn't look like this request comes from Slack.";
	echo $basestring . ",";
	echo $mysignature . ",";
	echo $_SERVER['HTTP_X_SLACK_SIGNATURE'];
}
?>
