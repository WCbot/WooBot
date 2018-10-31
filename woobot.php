<?php
$signingsecret = "d359026511347aa1caffacb200c332a8";
$basestring = "v0" . ":" . $_SERVER['HTTP_X_SLACK_REQUEST_TIMESTAMP'] . ":" . file_get_contents('php://input');
$mysignature = "v0=" . hash_hmac("sha256", $basestring, $signingsecret);
if ($mysignature == $_SERVER['HTTP_X_SLACK_SIGNATURE']) {
	$textarray = explode(" ", urldecode($_POST['text']));
	$mysqli = new mysqli("localhost", "root", "d[[P=.zOm7ur)QO<BXMewT+$%Lp{Mzhl", "woobot");
	if ($mysqli->connect_errno) { echo "Database error"; }
	if ($textarray[0] == "connect") {
		if (count($textarray) < 4) {
			header('content-type: text/plain');
			echo "Usage: /woobot connect <consumer key> <consumer secret> <woocomerce url>";
		} else {
			$dberror = false;
			$consumerkey = $textarray[1];
			$consumersecret = $textarray[2];
			$woo-rl = $textarray[3];
			$workspaceid = $_POST['team_id'];
			$exists_query = "SELECT EXISTS(SELECT * FROM users WHERE `workspace-id` = " . $_POST['team_id'] . ");";
			$exists_query_noselect = "EXISTS(SELECT * FROM users WHERE `workspace-id` = " . $_POST['team_id'] . ");";
			if (!$exists_result = $mysqli->query($exists_query)) {
				header('content-type: text/plain');
				$dberror = true;
				echo "Database error";
			}
			if ($dberror == true) { die(); }
			$exists_result = $exists_result->fetch_assoc();
			$exists_result = $exists_result[$exists_query_noselect];
			if ($exists_result == 1) {
				$update_query = "UPDATE users SET `consumer-key`=" . $consumerkey . ", `consumer-secret`=" . $consumersecret . ", `wc-url`=" . $woo-rl . " WHERE `workspace-id` = " . $workspaceid . ";";
				if (!$mysqli->query($update_query)) {
					header('content-type: text/plain');
					$dberror = true;
					echo "Database error";
				}
			} else {
				$set_query = "INSERT INTO users VALUES ('" . $workspaceid . "','" . $consumerkey . "','" . $consumersecret . "','" . $woo-rl . ");";
				if (!$mysqli->query($set_query)) {
					header('content-type: text/plain');
					$dberror = true;
					echo "Database error";
			}
			if ($dberror == true) {
				die();
			} else {
				$response_array = array(
				"username" => "WooBot",
				"channel" => $_POST['channel_id'],
				"response_type" => "in_channel",
				"text" => "Sucessfully connected!",
				"mrkdwn" => false,
				);
				}
			}
		}
	}
	if ($textarray[0] == "test") {
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
}
?>
