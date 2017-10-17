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
			$num = bcdiv($num, '256');
		} while (bccomp($num, '0'));
		return $result;
	}
	
	function Encrypt($msg, $data)
	{
		$this->data = base64_decode($data);
		$this->exp = base64_decode(json_decode($this->data, true)["exp"]);
		$this->mod = base64_decode(json_decode($this->data, true)["mod"]);
		$this->encryptData = array();
		$this->encryptData["msg"] = $msg;
		$this->encryptData["nounce"] = hash("crc32", rand(100000, 999999));
		$this->encodedMsg = $this->textDec(json_encode($this->encryptData));
		$this->encryptedData = $this->PowerMod($this->encodedMsg, $this->exp, $this->mod);
		return base64_encode($this->encryptedData);
	}
	
	function Decrypt($encryptedMsg, $data)
	{
		$this->data = base64_decode($data);
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


$rsa->generateKeys(128);

echo('<textarea style="width: 500px; height: 100px;">' . $rsa->publicKey . "</textarea><br>");
echo('<textarea style="width: 500px; height: 100px;">' . $rsa->privateKey . "</textarea><br>");


// BIT: 7250

/*

$encryptKey = "";
$decryptKey = "";

$msg = "RSA (Rivest–Shamir–Adleman) is one of the first practical public-key cryptosystems and is widely used for secure data transmission. In such a cryptosystem, the encryption key is public and it is different from the decryption key which is kept secret (private). ";

echo("Message: " . $msg . "<br>");

$enc = $rsa->Encrypt($msg, $encryptKey);


echo "encrypted_message: " . $enc . "<br>";

echo("decrypted_message: " . $rsa->Decrypt($enc, $decryptKey));

*/

?>
