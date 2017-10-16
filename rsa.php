<?php

class ColdChipMath {
	
	public function Addition($a, $b){
		$this->aSize = strlen($a);
		$this->bSize = strlen($b);
		$this->aArray = str_split($a);
		$this->bArray = str_split($a);
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
		$exp = base64_decode(json_decode($data, true)["exp"]);
		$mod = base64_decode(json_decode($data, true)["mod"]);
		return $this->PowerMod($msg, $exp, $mod);
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
		echo('<textarea style="width: 500px; height: 100px;">' . $this->publicKey . "</textarea><br>");
		echo('<textarea style="width: 500px; height: 100px;">' . $this->privateKey . "</textarea><br>");
	}
	
	function rsabase64encode($exponent, $modulus)
	{
		$this->packer = array();
		$this->packer["exp"] = base64_encode($exponent);
		$this->packer["mod"] = base64_encode($modulus);
		$this->packer["sign"] = hash("SHA512", $exponent . $modulus);
		return base64_encode(json_encode($this->packer));
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

$encryptKey = "eyJleHAiOiJOalUxTXpjPSIsIm1vZCI6Ik9Ea3dOamczTkRJeE9UYzVORE14TnprMk1qZzJPRGs0TmpZMk5URTFORFUyTWpFeU5qYzJPVEEyT0RBd05qazVNalF5TnpFeU1qazJNek0zTkRNM05UUTJOelV5T1RVd09ETTFNakk1T1RVM09UVTFOek14TVRBd01qYzBOakUyTVRjMU56VXhNVFUxTkRRME1ETTVPREl6TnpRMk9ERXdOell3T0RNeE56VTVOalV6TXpnek5ERTROekEyTXpBNE1EUTJOVEF5TmprNE9EVTFNRFkzT0RJd01EZ3dPREF3TWpRNU5UQXlOekkwT0RBd01EZ3hOakF6TXpVMU9UWXpNalV3T1RVNE16TTVPRE14TnpJMU5UTTROemM1TWpFd01qRXpOREl4Tnpnd056UXlOamcwTVRnd09UTTJPREk0T0RnM09UQTVNRFF4TnpRNU9EVTVPVEV6TnpZME5UZ3dORGMxTkRnMU5EY3lOak15TlRrMU5UUXpOelkzTXpNd016TTBNVE16TkRRM05qUTVNekU0T0RBNE56STUiLCJzaWduIjoiMTUwM2QxNTk5Y2VjMDBkMDU2ODBiOWFlYzMzN2ZjOWZhYTNhM2I5NWFmNTQ3ZTU4NjBhOTllY2FjYWY5YzU2NzMxY2I4ZTFlYWJjNTNhZDdmZTI1ZjQ3NWJlNWI4OWJlMmViZjhmYmFhZTIzYWI0MjljMjRkZjJiOWEwYjJlNDQifQ====";
$decryptKey = "eyJleHAiOiJNamt6TlRVM01EUTRPVEUxTWpBME1ETTBNelU1TVRjeE16STJNRGN4TWpnMU56UTVPVFF3TURVeE9UZzBNekF3TlRJM01EWTVPVGcwT1RBM05UZzRPRFU0TXpVeU56UXpNekExT1RNek5UVXdNemc0T1RrNE5EVTFNekUwTWpjMk1EUTROVGcyTmpjMU5UZ3hOVGs1TURrNU5qTTNNekl4TURreU5ESXdOVFUyTkRVMk9EVTNOamd4TURZMk5URTFPVEU1TXpFNU56UTNOVE13TlRrNE5ETTFNek16TnpFNE16QXpORGcxTURJeU56WTVPREEzT1RReU1qRTVPRGsxTVRBd01UazJOVEE1TURZek1ESXlNalkxTXpFM05qa3dPRE0yTlRnNU16ZzJNRGd5TkRVMU5qUXlOVFk0TmpjM09ETTNNelEyTmpBME56WTJNekUwTnpBM09ETTBOalExTmpjeE56QTBOakV5TlRFNE16a3hOalkxTURBM05URXlNRGsyT1RZNE5qa3hNVFl4T0RBeU9EY3dNamN6IiwibW9kIjoiT0Rrd05qZzNOREl4T1RjNU5ETXhOemsyTWpnMk9EazROalkyTlRFMU5EVTJNakV5TmpjMk9UQTJPREF3TmprNU1qUXlOekV5TWprMk16TTNORE0zTlRRMk56VXlPVFV3T0RNMU1qSTVPVFUzT1RVMU56TXhNVEF3TWpjME5qRTJNVGMxTnpVeE1UVTFORFEwTURNNU9ESXpOelEyT0RFd056WXdPRE14TnpVNU5qVXpNemd6TkRFNE56QTJNekE0TURRMk5UQXlOams0T0RVMU1EWTNPREl3TURnd09EQXdNalE1TlRBeU56STBPREF3TURneE5qQXpNelUxT1RZek1qVXdPVFU0TXpNNU9ETXhOekkxTlRNNE56YzVNakV3TWpFek5ESXhOemd3TnpReU5qZzBNVGd3T1RNMk9ESTRPRGczT1RBNU1EUXhOelE1T0RVNU9URXpOelkwTlRnd05EYzFORGcxTkRjeU5qTXlOVGsxTlRRek56WTNNek13TXpNME1UTXpORFEzTmpRNU16RTRPREE0TnpJNSIsInNpZ24iOiJjMGE0MmRhNTkxZDg3YTNmYThkYmIxMDYwYzg1MjgzOTczNDYxNGZlMTQyOThjYzI2MjI2OTFjOTc1ODhmYTkxNjVhMWM1MDEyODJhMDQ4ZjJlYjIwMDk4ZDNiYTY3OTZiZWQ1NDEzNWE3MzYyNGI1YTI5NTNiYWU0ODE2NDY2ZiJ9==";

$msg = "1234567890";

echo("Message: " . $msg . "<br>");

$enc = $rsa->encryptdecrypt($msg, $encryptKey);


echo "encrypted_message: " . $enc . "<br>";

echo("decrypted_message: " . $rsa->encryptdecrypt($enc, $decryptKey));

*/

?>
