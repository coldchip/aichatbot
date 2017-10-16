<?php

class ColdChipMath {
	
	public function Addition($a, $b){
		return gmp_add($a, $b);
	}
	
	public function PowerMod($a, $b){
		return gmp_powm($a, $b);
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
	
	private $publicKey;
	private $privateKey;
	
	private $defaultExponent = 65537;
	
	function __construct()
	{
		if (!extension_loaded('gmp')) {
			die("[FATAL_ERROR] This Library Requires PHP GMP Library, Please Enable It If You Had Installed It. ");
		}
	}
	
	function encryptdecrypt($msg, $data)
	{
		$data = base64_decode($data);
		$exp = json_decode($data, true)["exp"];
		$mod = json_decode($data, true)["mod"];
		return PowerMod($msg, $exp, $mod);
	}
	
	function generateKeys($insize)
	{	
		$size = ceil((($insize/2)/8)*2.421875);
		for($i = 0; $i < $size; $i++)
		{
			$randp .= mt_rand(1, 9);
			$randq .= mt_rand(1, 9);
		}

		$p = gmp_nextprime($randp);
		$q = gmp_nextprime($randq);
		
		$n = $this->Multiply($p, $q);
		$phi_n = $this->Multiply($this->Subtract($p, 1), $this->Subtract($q, 1));
		
		$d = $this->genPrivateKey($this->defaultExponent, $phi_n);
		
		$this->publicKey =  $this->rsabase64encode($this->defaultExponent, $n);
		$this->privateKey = $this->rsabase64encode($d, $n);
		
		
		echo("RSA KEY BIT SIZE: " . $insize . "<br>");
		echo('<textarea style="width: 500px; height: 100px;">' . $publicKey . "</textarea><br>");
		echo('<textarea style="width: 500px; height: 100px;">' . $privateKey . "</textarea><br>");
	}
	
	function rsabase64encode($exponent, $modulus)
	{
		$packer = array();
		$packer["exp"] = strval($exponent);
		$packer["mod"] = strval($modulus);
		return base64_encode(json_encode($packer));
	}
	
	function genPrivateKey($exponent, $p_n)
	{
		$x = 1;
		$y = 0;
		$e = $exponent;
		$phi_n = $p_n;
		do {
			$tmp = $this->Modulus($e, $phi_n);
			$q = $this->Divide($e, $phi_n);
			$e = $phi_n;
			$phi_n = $tmp;
			$tmp = $this->Subtract($x, $this->Multiply($y, $q));
			$x = $y;
			$y = $tmp;
		} while ($this->Compare($phi_n, '0') !== 0);
		if (bccomp($x, '0') < 0) {
			$x = $this->Addition($x, $p_n);
		}

		return $x;
	}
	
	
}

$rsa = new RSA();


echo($rsa->generateKeys(1024));

/*

$encryptKey = "eyJleHAiOiI2NTUzNyIsIm1vZCI6IjE0MjcyNzk3OTEzNDczNjA0NzI2NjQ3OTkzMzMxMDQ2OTQ2NTMyOTA4NzczMzI2NDA5MTY4NTAwODk3NTYxOTcyODM4MDY0NzI1NTA1NDQ4NjE2MjI5NzY4OTAwOTUxMTgwODI3MjU0MTAyMjgwODYxMjI3ODQ4Mzc3NzE1MzQ3MTc4NzMyOTk1MzEzODkxOTc5NTEzOTg1NTQ2NDU3OTI2Njk4NjE4NzQxMDA4NTYyMDk4NDAyNDExMjg3MjQ2MDA5MTIyNjEyMTY5MzQ4MDk2MjE2OTkwNDIzNDcwNjUyNTQxMDYyNjA0NTAxOTAzMDgxMDM1OTYxMjk1Nzg1MDUwNzk3Nzk4NTEyMTc2NDQwMDE4OTA1ODA1MTIxNzgwNzExMDYwNTA0NjE3ODcyMDM0MTM1MDEifQ==";
$decryptKey = "eyJleHAiOiIxMTc2NDE2MzcxOTU3ODUxNTY0OTU0MjU2ODM3NzUwNDIxODM0NzExMTgwMTIzNTExODY0MjM1NTk0MzczNDExNDI5ODI4MzExMjQ3NjIwOTIxNTQyNzkyMDk2ODE3OTUxNTE3MzUxNDM4MjY2OTQ0NzIxMDA2MzIyNjM4MjQ2NTIzMTg2NTk1MjY4MTE2OTY3NDM3MzA0ODM3Mjc3MDA3NDc2OTQ1ODEzNTIxNDgzMjk1NzY1MTc0OTI2NTU3NDA3MjkyMTA4OTQwOTgwMTQ5MTA1MDc0NjA4Njc3MDI5ODc0NDc3MzAyOTY3NDYyNzAyNDUwNTEyMDE5NjkzNTg3NDk2NDcwNTM2NDI5ODUxNjU3MjM3MDQ3ODAzNTk0MTUyNjg5NzgwMTg2NjQ3MjE1NDk4NTYxIiwibW9kIjoiMTQyNzI3OTc5MTM0NzM2MDQ3MjY2NDc5OTMzMzEwNDY5NDY1MzI5MDg3NzMzMjY0MDkxNjg1MDA4OTc1NjE5NzI4MzgwNjQ3MjU1MDU0NDg2MTYyMjk3Njg5MDA5NTExODA4MjcyNTQxMDIyODA4NjEyMjc4NDgzNzc3MTUzNDcxNzg3MzI5OTUzMTM4OTE5Nzk1MTM5ODU1NDY0NTc5MjY2OTg2MTg3NDEwMDg1NjIwOTg0MDI0MTEyODcyNDYwMDkxMjI2MTIxNjkzNDgwOTYyMTY5OTA0MjM0NzA2NTI1NDEwNjI2MDQ1MDE5MDMwODEwMzU5NjEyOTU3ODUwNTA3OTc3OTg1MTIxNzY0NDAwMTg5MDU4MDUxMjE3ODA3MTEwNjA1MDQ2MTc4NzIwMzQxMzUwMSJ9==";

$msg = "178786654345567567675666563454565767868543423567687687980980876543";

echo("Message: " . $msg . "<br>");

$enc = $rsa->encryptdecrypt($msg, $encryptKey);


echo "encrypted_message: " . $enc . "<br>";

echo("decrypted_message: " . $rsa->encryptdecrypt($enc, $decryptKey));

*/

?>

