<?php

class ColdChipMath {
	
	public function Addition($a, $b){
		return gmp_add($a, $b);
	}
	
	public function PowerMod($a, $b, $c){
		return gmp_powm($a, $b, $c);
	}
	
	public function Multiply($a, $b){
		return gmp_mul($a, $b);
	}
	
	public function Subtract($a, $b){
		return gmp_sub($a, $b);
	}
	
	public function Modulus($a, $b){
		return gmp_mod($a, $b);
	}
	
	public function Divide($a, $b){
		return gmp_div($a, $b);
	}
	
	public function Compare($a, $b){
		return gmp_cmp($a, $b);
	}
	
}

class RSA extends ColdChipMath {
	
	public $publicKey;
	public $privateKey;
	
	private $defaultExponent = 65537;
	
	function __construct()
	{
		if (!extension_loaded('gmp')) {
			die("[FATAL_ERROR] This Library Requires PHP GMP Library, Please Enable It If You Had Installed It. ");
		}
	}
	
	private function textDec($text)
	{
		$result = '0';
		$n = strlen($text);
		do {
			$result = bcadd(gmp_mul($result, '256'), ord($text{--$n}));
		} while ($n > 0);
		return $result;
	}
	
	private function decText($num)
	{
		$result = '';
		do {
			$result .= chr(bcmod($num, '256'));
			$num = gmp_div($num, '256');
		} while (bccomp($num, '0'));
		return $result;
	}
	
	function Encrypt($msg)
	{
		$this->data = base64_decode($this->publicKey);
		$this->exp = base64_decode(json_decode($this->data, true)["exp"]);
		$this->mod = base64_decode(json_decode($this->data, true)["mod"]);
		$this->encryptData = array();
		$this->encryptData["msg"] = $msg;
		$this->encryptData["nounce"] = hash("crc32", rand(100000, 999999));
		$this->encodedMsg = $this->textDec(json_encode($this->encryptData));
		if(strlen($this->encodedMsg) <= strlen($this->mod)) {
			$this->encryptedData = $this->PowerMod($this->encodedMsg, $this->exp, $this->mod);
			return base64_encode($this->encryptedData);
		} else {
			return false;
		}
		
	}
	
	function Decrypt($encryptedMsg) {
		$this->data = base64_decode($this->privateKey);
		$this->exp = base64_decode(json_decode($this->data, true)["exp"]);
		$this->mod = base64_decode(json_decode($this->data, true)["mod"]);
		$this->decryptedMsg = $this->PowerMod(base64_decode($encryptedMsg), $this->exp, $this->mod);
		$this->decodedMsg = json_decode($this->decText($this->decryptedMsg), true);
		return $this->decodedMsg["msg"];
		
	}
	
	function generateKeys($bitLen)
	{	
		$this->size = ceil((($bitLen/2)/8)*2.421875);
		for($i = 0; $i < $this->size; $i++)
		{
			$this->randp .= mt_rand(1, 9);
			$this->randq .= mt_rand(1, 9);
		}

		$this->p = gmp_nextprime($this->randp);
		$this->q = gmp_nextprime($this->randq);
		
		$this->n = $this->Multiply($this->p, $this->q);
		$this->phi_n = $this->Multiply($this->Subtract($this->p, 1), $this->Subtract($this->q, 1));
		
		$this->d = $this->genPrivateKey($this->defaultExponent, $this->phi_n);
		
		$this->publicKey =  $this->rsabase64encode($this->defaultExponent, $this->n);
		$this->privateKey = $this->rsabase64encode($this->d, $this->n);
		
		return true;
		
	}
	
	function rsabase64encode($exponent, $modulus)
	{
		$this->packer = array();
		$this->packer["type"] = "COLDCHIPRSA";
		$this->packer["version"] = "1.2";
		$this->packer["exp"] = base64_encode($exponent);
		$this->packer["mod"] = base64_encode($modulus);
		$this->packer["sign"] = hash("SHA512", $exponent . $modulus);
		return base64_encode(json_encode($this->packer));
	}
	
	function genPrivateKey($exponent, $phi_n)
	{
		$x = 1;
		$y = 0;
		$this->Exponent = $exponent;
		$this->Phi_n = $phi_n;
		do {
			$tmp = $this->Modulus($this->Exponent, $this->Phi_n);
			$q = $this->Divide($this->Exponent, $this->Phi_n);
			$this->Exponent = $this->Phi_n;
			$this->Phi_n = $tmp;
			$tmp = $this->Subtract($x, $this->Multiply($y, $q));
			$x = $y;
			$y = $tmp;
		} while ($this->Compare($this->Phi_n, '0') !== 0);
		if ($this->Compare($x, '0') < 0) {
			$x = $this->Addition($x, $phi_n);
		}

		return $x;
	}
	
	
}

$rsa = new RSA();

if($_SERVER['REQUEST_METHOD'] == "POST")
{
	if($_POST["mode"] == "encrypt")
	{
		$rsa->publicKey = $_POST["pubkey"];
		$encryptedData = $rsa->Encrypt($_POST["msg"]);
		if($encryptedData) {
			die('{"data":"' . $encryptedData . '"}');
		} else {
			die('{"data":"Message is too long. "}');
		}
	}
	
	if($_POST["mode"] == "decrypt")
	{
		$rsa->privateKey = $_POST["prikey"];
		die('{"data":"' . $rsa->Decrypt($_POST["msg"]) . '"}');
	}
	
	if($_POST["mode"] == "genkeys")
	{
		$rsa->generateKeys(2048);
		$packer = array();
		$packer["pubkey"] = $rsa->publicKey;
		$packer["prikey"] = $rsa->privateKey;
		die(json_encode($packer));
	}
}



?>
<!DOCTYPE html>
<html>
<head>
<title>ColdChip - RSA</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />		
<link rel="shortcut icon" type="image/png" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII="/>
<meta charset="utf-8">	
<script>
function decrypt()
{
	var prikey = document.getElementById("prikey").value;
	var msg = document.getElementById("msg").value;
	if(!prikey)
	{
		alert("Private Key is empty. ");
		msg.value = "";
		return false;
	}
	var http = new XMLHttpRequest();
	http.open("POST", window.location.pathname + window.location.search, true);
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.send("msg=" + encodeURI(msg) + "&prikey=" + encodeURI(prikey) + "&mode=decrypt");
	http.onreadystatechange = function()
	{
		if(this.readyState == 4 && this.status == 200)
		{
			var result = JSON.parse(http.responseText);
			document.getElementById("msg").value = result["data"];
		}
	};
}

function encrypt()
{
	var pubkey = document.getElementById("pubkey").value;
	var msg = document.getElementById("msg").value;
	if(!pubkey)
	{
		alert("Public Key is empty. ");
		msg.value = "";
		return false;
	}
	var http = new XMLHttpRequest();
	http.open("POST", window.location.pathname + window.location.search, true);
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.send("msg=" + encodeURI(msg) + "&pubkey=" + encodeURI(pubkey) + "&mode=encrypt");
	http.onreadystatechange = function()
	{
		if(this.readyState == 4 && this.status == 200)
		{
			var result = JSON.parse(http.responseText);
			document.getElementById("msg").value = result["data"];
		}
	};
}

function genkeys()
{
	var http = new XMLHttpRequest();
	http.open("POST", window.location.pathname + window.location.search, true);
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.send("mode=genkeys");
	http.onreadystatechange = function()
	{
		if(this.readyState == 4 && this.status == 200)
		{
			var result = JSON.parse(http.responseText);
			document.getElementById("pubkey").value = result["pubkey"];
			document.getElementById("prikey").value = result["prikey"];
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
	margin:0;
	padding:0;
}.text{
	color: #505050;
	text-align: center;
	margin-top: 0px;
	margin-bottom: 0px;
}.textarea{
	box-sizing:border-box;
	text-align: left;
	width:100%;
	height: 80px;
	font-size: 18px;
	font-weight: normal;
	color: #3f3f3f;
	border: 1px solid #bbb;
	padding-left: 5px;
	border-radius: 3px;
	margin: 1px 0px 15px 0px;
	box-shadow: inset 0px 1px 2px rgba(27,31,35,0.075);
}.button{
	font-weight: bold;
	width: 100%;
	height: 38px;
	margin: 5px 0px;
	color: #fff;
	border: none;
	background-color: #269f42;
	font-size:15px;
	border-radius: 3px;
	box-sizing:border-box;
}.header{
	display:inline-block;
	visibility:visible;
	width:100%;
	height:50px;
	background: #fff;
	margin-bottom:10px;
	box-shadow:0 5px 10px #aaa;
	position: fixed;
}.content{
	padding-top: 80px;
	width: 95%;
	margin: auto;
}
</style>
</head>
<body>
<div class="header">
<p class="text" style="float: left; margin-left: 20px; font-size: 20px; font-weight: 300; margin-top: 12px;">ColdChip RSA</p>
</div>
<div class="content">
<textarea class="textarea" placeholder="Public Key / Encryption Key. " id="pubkey"></textarea>
<textarea class="textarea" placeholder="Private Key / Decryption Key. " id="prikey"></textarea>
<textarea class="textarea" placeholder="Message. " id="msg"></textarea>
<button class="button" onClick="encrypt()">Encrypt</button>
<button class="button" onClick="decrypt()">Decrypt</button>
<button class="button" onClick="genkeys()">Generate Keys</button>

</div>
</body>
</html>
