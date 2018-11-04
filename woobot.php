<?php
// WooBot
// Requires: automattic/woocommerce

require __DIR__ . '/vendor/autoload.php';
use Automattic\WooCommerce\Client;

$signingsecret = "d359026511347aa1caffacb200c332a8";
$basestring = "v0" . ":" . $_SERVER['HTTP_X_SLACK_REQUEST_TIMESTAMP'] . ":" . file_get_contents('php://input');
$mysignature = "v0=" . hash_hmac("sha256", $basestring, $signingsecret);
if ($mysignature == $_SERVER['HTTP_X_SLACK_SIGNATURE']) {
	$textarray = explode(" ", urldecode($_POST['text']));
	$mysqli = new mysqli("localhost", "root", "~~~obfuscated~~~", "woobot");
	if ($mysqli->connect_errno) { echo "Database error 0"; }
	if ($textarray[0] == "connect") {
		if (count($textarray) != 5) {
			header('content-type: text/plain');
			print_r($textarray);
			echo count($textarray);
			echo "Usage: /woobot connect <consumer key> <consumer secret> <woocomerce url> <enable subscriptions>";
			die();
		} else {
			$dberror = false;
			$consumerkey = $textarray[1];
			$consumersecret = $textarray[2];
			$woorl = $textarray[3];
			$enablesubs = $textarray[4];
			if ($enablesubs == 1 || $enablesubs == 0) { header('content-type: text/plain'); echo "<enable subscriptions> should be either 0 or 1"; die(); }
			$workspaceid = $_POST['team_id'];
			$exists_query = "SELECT EXISTS(SELECT * FROM users WHERE `workspace-id` = '" . $_POST['team_id'] . "');";
			$exists_query_noselect = "EXISTS(SELECT * FROM users WHERE `workspace-id` = '" . $_POST['team_id'] . "')";
			if (!$exists_result = $mysqli->query($exists_query)) {
				header('content-type: text/plain');
				$dberror = true;
				echo "Database error 1" . $mysqli->error;
				die();
			}
			if ($dberror == true) { die(); }
			$exists_result = $exists_result->fetch_assoc();
			$exists_result = $exists_result[$exists_query_noselect];
			if ($exists_result == 1) {
				$update_query = "UPDATE users SET `consumer-key`='" . $consumerkey . "', `consumer-secret`='" . $consumersecret . "', `wc-url`='" . $woorl . "', `enable-subs`='" . $enablesubs . "' WHERE `workspace-id` = '" . $workspaceid . "';";
				if (!$mysqli->query($update_query)) {
					header('content-type: text/plain');
					$dberror = true;
					echo "Database error 2";
					die();
				}
			} else {
				$set_query = "INSERT INTO users VALUES ('" . $workspaceid . "','" . $consumerkey . "','" . $consumersecret .  "','" . $woorl . "','" . $enablesubs . "');";
				if (!$mysqli->query($set_query)) {
					header('content-type: text/plain');
					$dberror = true;
					echo "Database error 3 " . $mysqli->error;
					die();
				}
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
	elseif ($textarray[0] == "email") {
		if (count($textarray) != 2) {
			header('content-type: text/plain');
			echo "Usage: /woobot email <email address>";
			die();
		} else {
			$email = $textarray[1];
			$get_userdata_query = "SELECT * FROM users WHERE `workspace-id` = '" . $_POST['team_id'] . "';";
			if (!$get_userdata_result = $mysqli->query($get_userdata_query)) {
				$dberror = true;
				echo "Database error 4";
			}
			if ($dberror == true) { die(); }
			$get_userdata_result = $get_userdata_result->fetch_assoc();
			$consumerkey = $get_userdata_result['consumer-key'];
			$consumersecret = $get_userdata_result['consumer-secret'];
			$woorl = $get_userdata_result['wc-url'];
			$woocommerce = new Client($woorl, $consumerkey, $consumersecret, array('wp_api' => true, 'version' => 'wc/v3'));
			if (!$response = $woocommerce->get('customers?email=' . $email . '&role=all')) {
				header('content-type: text/plain');
				echo "WooCommerce error. Make sure there is a user with the email specified, and check your consumer key, consumer secret and WooCommerce URL are correct.";
				die();
			}
			print_r($response);
		}
	}
	elseif ($textarray[0] == "test") {
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
