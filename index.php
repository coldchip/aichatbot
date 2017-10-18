<?php

class AI {
	
	public $feelings = array();
	
	public $root;
	
	public $databaseDir;
	
	function __construct() {
		$this->feelings["anger"]    = 0;
		$this->feelings["sadness"]  = 0;
		$this->feelings["joy"]      = 0;
		$this->feelings["disgust"]  = 0;
		$this->feelings["surprise"] = 0;
		$this->feelings["trust"]    = 0;
		
		$this->root = getcwd() . "/" . "ai_db" . "/";
		
		if(!is_dir($this->root))
		{
			mkdir($this->root) or die("Insufficent permissions to create a database folder. ");
		}
		
		$this->loadEmotionData();
	}
	
	private function loadEmotionData() {
		if(file_exists($this->root . "/emotions.ai")) {
			$this->feelings = json_decode($this->loadDb("emotions.ai"), true);
			return true;
		} else {
			file_put_contents($this->root . "emotions.ai", json_encode($this->feelings));
			return true;
		}
	}
	
	private function saveEmotionData() {
		if(file_exists($this->root . "/emotions.ai")) {
			file_put_contents($this->root . "emotions.ai", json_encode($this->feelings, true));
			return true;
		} else {
			file_put_contents($this->root . "/emotions.ai", json_encode($this->feelings));
			return true;
		}
	}
	
	private function detectQuestion($text) {
		$this->text = $this->formatInput($text);
		$questionTypes = $this->loadDb("questions.ai");
		$this->questionTypes = explode(PHP_EOL, $questionTypes);
		foreach($this->questionTypes as $questionTypesArray)
		{
			if(strpos($this->text, $this->formatInput($questionTypesArray)) !== false)
			{
				return true;
			}
		}
	}
	
	public function Process($text) {		
		if($this->detectQuestion($text)) {
			return "That is a question. ";
		} else {
			return "That is NOT a question. ";
		}
	}
	
	private function formatInput($text)
	{
		return strtolower($text);
	}
	
	private function ThrowNewError($err) {
		die($err);
	}
	
	private function loadDb($name) {
		$data = file_get_contents($this->root . $name);
		if($data)
		{
			return $data;
		} else {
			$this->ThrowNewError("File " . $name . " does not exist. ");
		}
	}

}

if(strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
	
	$ai = new AI();
	
	$data = $_POST["data"];
	
	$root = $_SERVER["DOCUMENT_ROOT"];
	
	$packer = array();
	$packer["msg"] = $ai->Process($data);
	$packer["cs"] = md5(uniqid());
	die(json_encode($packer));
	
}

?>
<!DOCTYPE html>
<html>
<head>
<title>ColdChip - AI</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />		
<link rel="shortcut icon" type="image/png" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII="/>
<meta charset="utf-8">	
<script>
function send()
{
	var data = document.getElementById("data").value;
	document.getElementById("res").innerHTML = "Processing... ";
	var http = new XMLHttpRequest();
	http.open("POST", window.location.pathname + window.location.search, true);
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.send("data=" + encodeURI(data));
	http.onreadystatechange = function()
	{
		if(this.readyState == 4 && this.status == 200)
		{
			var result = JSON.parse(http.responseText);
			document.getElementById("res").innerHTML = result["msg"];
		}
	};
}
</script>
<style>
*{
	-webkit-appearance: none;
	-moz-appearance: none;
	-ms-appearance: none;
	-o-appearance: none;
	direction: ltr;
	outline:0;
	font-family: Arial, sans-serif;
}body{
	background: #f0f0f0;
}.text{
	color: #505050;
	text-align: center;
	margin-top: 0px;
	margin-bottom: 0px;
}.form{
	display: block;
	margin: 100px auto;
	padding: 15px 15px;
	max-width: 270px;
	background: #fff;
	border-radius: 3px;
	border: 1px solid #d1d5da;
}.input{
	box-sizing:border-box;
	text-align: left;
	width:100%;
	height: 38px;
	font-size: 18px;
	font-weight: normal;
	color: #3f3f3f;
	border: 1px solid #bbb;
	padding-left: 5px;
	border-radius: 3px;
	margin: 2px 0px 15px 0px;
	box-shadow: inset 0px 1px 2px rgba(27,31,35,0.075);
}.inputbutton{
	font-weight: bold;
	width: 100%;
	height: 38px;
	margin: 2px 0px;
	color: #fff;
	border: none;
	background-color: #269f42;
	font-size:15px;
	border-radius: 3px;
	box-sizing:border-box;
}
</style>
</head>
<body id="body">
<form class="form" id="form" onsubmit="send(); return false;">
	<p class="text" id="res" style="font-size: 1em; height: 20px;"></p>
	<input type="text" id="data" class="input" autocomplete="off" autocorrect="off" autocapitalize="off" placeholder="Talk to me. "><br>
	<input type="button" value="Send" onClick="send()" class="inputbutton"><br>
</form>
</body>
</html>