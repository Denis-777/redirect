<?php
include "config.php";
include "ShortUrl.php";


 $url = $_POST["url"];

try {
    $pdo = new PDO("mysql:host=" . DB_HOST .
        ";dbname=" . DB_DATABASE,
        DB_USERNAME, DB_PASSWORD);
}
catch (\PDOException $e) {
    trigger_error("Ошибка: не могу установить соединение с базой данных.");
    exit;
}

$shortUrl = new ShortUrl($pdo);

try {
	$code = $shortUrl->urlToShortCode($url);
	echo json_encode($code);  
    exit;
}
catch (\Exception $e) {	
    header("Location: /error");
    exit;
}

