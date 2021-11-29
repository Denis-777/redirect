<?php
class ShortUrl
{
    protected static $table = "short_urls";
    protected static $checkUrlExists = true;

    protected $pdo;
    protected $timestamp;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->timestamp = $_SERVER["REQUEST_TIME"];
    }

    public function urlToShortCode($url) {
		
        $urlData = parse_url($url);
		
		if (empty($url)) {
			try{
            throw new \Exception("Не получен адрес URL.");}
			catch (Exception $e) {
				return "Не получен адрес URL.";
			}
        }		
		if ($urlData['scheme'] != 'http' && $urlData['scheme'] != 'https') {
			try{
            throw new \Exception("Не получен адрес URL.");}
			catch (Exception $e) {
				return "Http имеет неправильный формат.";
			}
        }		
		if (empty($urlData["path"]) || $urlData["path"] == '/') {
			try{
            throw new \Exception("Подстрока пуста.");}
			catch (Exception $e) {
				return "Подстрока пуста.";
			}
        }
		
		if ($this->validateUrlFormat($url) == false) {
			try{
            throw new \Exception(
			"Адрес URL имеет неправильный формат.");}
			catch (Exception $e) {
				 return "Адрес URL имеет неправильный формат.";				
			}
				
        }

           
		
        $shortCode = $this->urlExistsInDb($url);
		
		
        if ($shortCode == false) {
            $shortCode = $this->createShortCode($url);
			
        }
		else{
			$urlData = parse_url($url);
			$shortCode = $urlData['scheme'] . "://" . $urlData['host'] . "/" . $shortCode;	
		}	
        return $shortCode;
    }

    protected function validateUrlFormat($url) {
        return filter_var($url, FILTER_VALIDATE_URL,
            FILTER_FLAG_HOST_REQUIRED);
    }

    protected function verifyUrlExists($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return (!empty($response) && $response != 404);
    }

    protected function urlExistsInDb($url) {
        $query = "SELECT short_code FROM " . self::$table .
            " WHERE long_url = :long_url LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $params = array(
            "long_url" => $url
        );
        $stmt->execute($params);

        $result = $stmt->fetch();
        return (empty($result)) ? false : $result["short_code"];
    }

    protected function createShortCode($url) {
		
        $id = $this->insertUrlInDb($url);		
        $shortCode = $this->convertIntToShortCode($id);		
        $this->insertShortCodeInDb($id, $shortCode);
		$urlData = parse_url($url);
		$shortCode = $urlData['scheme'] . "://" . $urlData['host'] . "/" . $shortCode;			
		
        return $shortCode;
    }

    protected function insertUrlInDb($url) {
        $query = "INSERT INTO " . self::$table .
            " (long_url, date_created) " .
            " VALUES (:long_url, :timestamp)";
        $stmnt = $this->pdo->prepare($query);
        $params = array(
            "long_url" => $url,
            "timestamp" => $this->timestamp
        );
        $stmnt->execute($params);

        return $this->pdo->lastInsertId();
    }

   
	 protected function convertIntToShortCode($id) {
		 
		$chars = "123456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ";
        $id = intval($id);

        if ($id < 1) {
           echo "ERROR1";
        }

        $length = strlen($chars);
       
        if ($length < 10) {
            echo "ERROR2";
        }

        $code = "";
        while ($id > $length - 1) {
            
            $code = $chars[(int)fmod($id, $length)] . $code;
           
            $id = floor($id / $length);
        }
        
        $code = $chars[(int)$id] . $code;

        return $code;
    
    }

    protected function insertShortCodeInDb($id, $code) {
        if ($id == null || $code == null) {
            throw new \Exception("Параметры ввода неправильные.");
        }
        $query = "UPDATE " . self::$table .
            " SET short_code = :short_code WHERE id = :id";
        $stmnt = $this->pdo->prepare($query);
        $params = array(
            "short_code" => $code,
            "id" => $id
        );
        $stmnt->execute($params);

        if ($stmnt->rowCount() < 1) {
            throw new \Exception(
                "Строка не обновляется коротким кодом.");
        }

        return true;
    }
}
