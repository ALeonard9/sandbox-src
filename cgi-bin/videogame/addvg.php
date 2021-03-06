<?php

if(!isset($_SESSION)) {
  session_start();
} ;
ob_start();

require '../composer/vendor/autoload.php';
include '../connectToDB.php';
include 'functions/functions.php';

$id = $_GET['id'];
$title = urldecode($_GET['title']);
$poster_id = $_GET['poster'];

if (!isset($_SESSION['twitch_token'])){
	twitchAuth();
}

$twitch_client_id= getenv('TWITCH_CLIENT_ID');
$twitch_client_auth= $_SESSION['twitch_token'];

$headers = array(
	"Client-ID" => $twitch_client_id,
	"Authorization" => "Bearer ".$twitch_client_auth,
	"Accept" => "application/json"
);

$data = "fields url; where id = $poster_id;";

$body = Unirest\Request\Body::form($data);

$response = Unirest\Request::post('https://api.igdb.com/v4/covers', $headers, $body);

$json = json_decode($response->raw_body, true);

if (count($json) == 1) {
	$poster = $json[0]['url'];
} else {
	$poster = 'N/A';
}

$complete = $_GET['complete'];
if ($poster == 'N/A' or is_null($poster)) {
	$poster = 'https://upload.wikimedia.org/wikipedia/en/f/f9/No-image-available.jpg';
}
$user_id = $_SESSION['userid'];
$check = "SELECT * FROM orion.videogames where igdb='".$id."';";
$stmt = $db->prepare($check);
$result = $stmt->execute();
if ( false===$result ) {
	error_log( serialize ($stmt->errorInfo()));
}
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( !$row){
	$stmt = $db->prepare("INSERT INTO `orion`.`videogames` (`igdb`, `title`, `poster_url`) VALUES (:id, :title, :poster)");
	$stmt->bindParam(':id', $id);
	$stmt->bindParam(':title', $title);
	$stmt->bindParam(':poster', $poster);
	$result = $stmt->execute();
	if ( false===$result ) {
		error_log( serialize ($stmt->errorInfo()));
	}
	$row_id =  $db->lastInsertId();
	updateVG($id);
} else {
	$row_id = $row['id'];
}

if (isset($_SESSION['userid']))
	{

		$stmt = $db->prepare("INSERT INTO orion.g_user_videogames (`user_id`, `videogames_id`, `rank`, `completed`, `g_first`) VALUES (:user, :row, '0', :complete, now())");
		$stmt->bindParam(':user', $user_id);
		$stmt->bindParam(':row', $row_id);
		$stmt->bindParam(':complete', $complete);
		$result = $stmt->execute();
		if ( false===$result ) {
            error_log( serialize ($stmt->errorInfo()));
        }
  	header("Location: videogame.php");
		exit;
	}
else
	{
	header("location: ../users/signin.php");
	}
?>
