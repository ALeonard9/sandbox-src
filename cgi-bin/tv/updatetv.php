<?php

require '../composer/vendor/autoload.php';
include '../connectToDB.php';
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT * FROM orion.tv";
$query = $db->query($sql);

foreach($query as $item){
	$searchafter = $item['imdb'];
	$response = Unirest\Request::get("http://api.tvmaze.com/lookup/shows?imdb=$searchafter",
		array(
			"Accept" => "application/json"
		)
	);
	$json = json_decode($response->raw_body, true);
	if ($json['status'] != $item['status']) {
		try {
			$stmt = $db->prepare("UPDATE `orion`.`tv` SET `status`=:status WHERE `id`=:id");
			$stmt->bindParam(':id', $id);
			$stmt->bindParam(':status', $json['status']);
			$stmt->execute();
			echo $item['title']." updated to ".$item['status'];
		} catch (PDOException $e) {
				echo 'Connection failed: ' . $e->getMessage();
		}
	}
}
?>
