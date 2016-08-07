<?php
session_start();
error_reporting(0);
function genkeys($bit)
{
   $rsabitsize = $bit;
   //325
   //6.3015
   $psize = "200";
   for($i=0;$i<$rsabitsize;$i++){
      $p .= mt_rand(0, 9);
   }
    $qsize = "200";
   for($a=0;$a<$rsabitsize;$a++){
      $q .= mt_rand(0, 9);
   }
     $esize = "100";
   for($w=0;$w<$esize;$w++){
      $e .= mt_rand(0, 9);
   }
   $p = gmp_nextprime($p);
   $q = gmp_nextprime($q);
   $n = gmp_mul($p, $q);
   $th = gmp_sub($p, "1");
   $tw = gmp_sub($q, "1");
   $ton = gmp_mul($th, $tw);
   $mod = $ton;
   $num = $e;
   $nn = gmp_mul($p, $q);
   $x = '1';
   $y = '0';
   $num1 = $mod;
		do {
			$tmp = gmp_mod($num, $num1);
			$q = gmp_div($num, $num1);
			$num = $num1;
			$num1 = $tmp;
 
			$tmp = gmp_sub($x, gmp_mul($y, $q));
			$x = $y;
			$y = $tmp;
		} while (bccomp($num1, '0'));
		if (bccomp($x, '0') < 0) {
			$x = gmp_add($x, $mod);
		}
		$enc = gmp_powm("2048", $e, $nn);
        $data = gmp_powm($enc, $x, $n);
   if($data == "2048")
   {
   $timepacked = $expiredate . "#" . $expiremonth . "#" . $expireyear;
   $ok = base64_encode($e . "#" . $n . "#" . $timepacked . "#coldchiprsa") . "#" . base64_encode($x . "#" . $n . "#coldchiprsa");
   return $ok;
   }else
   {
	   $dk = "reload";
	 return $dk;
   }
}
if(!empty($_POST["genkeys"]))
{
	
die(genkeys(162));

}

$pukey = $_POST["pub"];
$prkey = $_POST["pri"];
$pudata = explode("#", base64_decode($pukey));
$prdata = explode("#", base64_decode($prkey));

$et = $pudata[2] . "/" . $pudata[3] . "/" . $pudata[4];
		if($pudata[5] !== "coldchiprsa" && !empty($_POST["pub"]))
{
//die("Public key is not valid" . $pudata[5]);

}
$separator = "0000000000000000000000000000000000000000000000000000000000000";
function text2num($text)
{
	$result = '0';
		$n = strlen($text);
		do {
			$result = bcadd(gmp_mul($result, '256'), ord($text{--$n}));
		} while ($n > 0);
		return $result;
}
function num2text($num)
{
	$result = '';
		do {
			$result .= chr(bcmod($num, '256'));
			$num = bcdiv($num, '256');
		} while (bccomp($num, '0'));
		return $result;
}


	
	//	echo("Encrypted data: " . $enc . "<br />" . "Decrypted text: " . $dec);
		if(!empty($_POST["data"] && $_POST["encrypt"]))
		{
				$datt = $_POST["data"];
	$str = stripslashes(strip_tags($datt));
	$str = preg_replace("/[^A-Za-z0-9 ]/", '', $str);

	$strnum = text2num($str . $separator . uniqid());
	$encnum = gmp_powm($strnum, $pudata[0], $pudata[1]);
	$enc = base64_encode($encnum);


            die($enc);
		}
		elseif(!empty($_POST["data"] && $_POST["decrypt"]))
		{
		
				$data = base64_decode($_POST["data"]);
	$num = gmp_powm($data, $prdata[0], $prdata[1]);
	$decnum = num2text($num);
	$decdata = explode($separator, $decnum);
	$dec = $decdata[0];
	die($dec);
		
			
		}
		else
		{
			
		}
	
?>
<!DOCTYPE html>
<html>
<head>
<title>ColdChip</title>

<script src="ajax.min.js"></script>
<script>
var edata;
function encrypt()
{
	
	var data = $('#ta').val();
	var pub = $('#pub').val();
	var pri = $('#pri').val();
		if(data == '')
	{
		alert("Can't be blank.");
	}
else
{
	
	var encrypt = "true";
      
$.ajax({
			type: 'post',
			url: 'rsa.php',
			data: {
				pub:pub,
				pri:pri,
			data:data,
			encrypt:encrypt
			},
			success: function (response) {
				edata = response;
				var data = document.getElementById("ta").value = response;
				
			}
		});
}
}
var ddata;
function decrypt()
{
	
	var pub = $('#pub').val();
	var pri = $('#pri').val();
	var data = $('#ta').val();
	if(data == '')
	{
		alert("Can't be blank.");
	}
else
{

      var decrypt = "true";
$.ajax({
			type: 'post',
			url: 'rsa.php',
			data: {
				pub:pub,
				pri:pri,
			data:data,
			decrypt:decrypt
			},
			success: function (response) {
			if(response == "invalid")
			{
				alert("Invalid code to decode.");
				return;
			}
			ddata = response;
				var data = document.getElementById("ta").value = response;
				
			}
		});
}
}
function clear()
{
	document.getElementById("ta").value = "j";
}
function gen()
{
	document.getElementById("pub").value = "Generating keys... ";
				document.getElementById("pri").value = "Generating keys... ";
				var genkeys = "true";
	$.ajax({
			type: 'post',
			url: 'rsa.php',
			data: {
				genkeys:genkeys
			},
			success: function (response) {
			
				if(response == "reload")
				{
					gen();
				}
				else
				{
				var data = response.split("#");	
				document.getElementById("pub").value = data[0];
				document.getElementById("pri").value = data[1];
				}
			}
		});
}
function show()
{

window.location.href = "pubkey.txt";


}
</script>
<link rel="icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABLAAAAJ2AQMAAAB1jukfAAAAA1BMVEX///+nxBvIAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAc0lEQVR4nO3BAQ0AAADCoPdPbQ43oAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAXg1zqQABbTDF3gAAAABJRU5ErkJggg==
" type="image/gif" sizes="16x16">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
<style>

body
{
	text-align: center;
	font-family: Trebuchet MS;
	font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
	font-weight: 200;
	color: #595959;
}
.pt
{
	font-size: 1.7em;
}
.foot
{
	font-size: 1.3em;
}
textarea
{
	width: 250px;
	height: 80px;
	font-family: Trebuchet MS;
	font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
	font-weight: 200;
	color: #595959;
	box-sizing: border-box;
	border-radius: 3px;
	outline: 0;
}
.en
{
	border: 1px solid #595959;
	appearance: none;
	background-color: #cccccc;
	border-radius: 3px;
	width: 250px;
	height: 40px;
	font-family: Trebuchet MS;
	font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
	font-weight: 200;
	color: #595959;
	outline: 0;
	margin-bottom: 5px;
}
.warp
{
width: 250px;
margin: auto auto;	
}
</style>

</head>
<body>
<div class="warp">


<label for="pub">Encryption key: </label>
<textarea id="pub">NTc1NjI3Mjc1NTI5NDY1Njc4MDU2MDE2MTU2NDgxMzg2MzY0Njg5MzAxODkyOTU3NTA3MjI3OTk4Njc5NzUzMzgwMTA2NTg3NDcwNzgzODg2MDQ0NjY4NzEwNDk4MDMzNzQ1NyM1OTY4NzIwMjMzMTAwNTYxODMyNTYwNzM0MjQwODU0NTEwOTQ0NjA0MjMzMjYxMTAwNjg2ODg2NzkyMzE2ODQ1MDU5ODE5NTA1NDIxMjkwNTg1ODEzNzY0NTQ2MjI4MDU3ODU2NzA1ODEzMTM4OTkyNjI0OTQwMjU3MzMwOTcwMjMxMjI3MTkzMzI1MjU0NTg2MjY1NTE3NDE4ODQ5OTI5NjU5MTE3NDUzMjY3MTMzNjU2NTczNzgwODcwMDA0NDg1MTE1OTU2NjQyMTcwNzI4NDg2NzM3MzcwMDAwODQyODU1NzY3MTQxMTM5Mzk4NTgwNDAwODA4MDk0NzQ5OTk1NjA1NjM0NjA5MDM4ODM3NTI2OTI5NDk2MzIxMjY1NzUwMjMzNTQ3NTYzNTgxNjI1ODE1MzYwNTIzNDc1NTY1OTE0MDg2MDMyMDIwMjE4NTEzOTEwNTM5OTM5MTYzOTI4NzcyMDI3Nzg2MzY0NTEwNjI0MTA2MDk1OTA2NjM0NDc4NDY4NzM4NzY2NDk5NjA3NTA1MDQ5MzU1MDA2MzA4ODcyNTA4OTI1NTkyMTc2NzI0MjA0NDg0NDg2MzYyODA3NzQyNzg1OTQ2MDUyMTMwNDE1NTE3NzI1NjIwODY4NDQ2NjIyMzgyNTA2Mjc3MDAzMzg0MTc4NTk0Nzg0MzA3Njg3OTA1NjU0ODQwNTM1NzkyNjkwMDQ1ODgyMTUxMDcxNDQ4MjkwODQ5MjAxNjQ2MjI0NTc0NTA4OTU0ODk5MTU0MDM0MTIyNjAwMzAzNTI4MjM5NzMzNDUxOTExMTAzOTU4Mzc2NjM1NjgwNzU4ODI3OTUxODg4OTA4MDQ4MTM0MzgzNDI5NjcxOSM2IzkjMTYjY29sZGNoaXByc2E=</textarea><br/>
<label for="pri">Decryption key: </label>
<textarea id="pri">MzI2MzgyNzIzNzcyMzk3ODgxMzg0NDMyOTY2MjIwNzUwMjEwNTg1ODQyMTQwNzU3NDY5NjM0OTQ0MzAyNTY5ODI3ODA5Njc2MTAzOTQ4ODE1NDU1NDk4NTMwMDg5NDE3NzM4Nzk2NDY2NTIwNTQ4OTMxNjkzNzI3OTE1OTUyMTYyMjE2OTc0NTY5MjAyMzEwMzM0NTU2NTk3NzQ0ODI3MDk4MzU4MjIwMjU4NDUxMDY1MTQxNzU4MTQ5ODAyODE0OTk1NjgzODk4NjUwOTQ3MTY2NDgzNDcxMTM1MjAyNDg0NDE2MTE4NTg5MTA4MDU1MjU3MTIyOTQ1MjE2MTY5NTc0NTEzNTcyMjU0MDg1MzA5MTE5NTA5MTg0NDU3ODA2Mzc4OTUxMTc5NjkxMzU3NTM2OTg5NzQxMDAxNDA0NzQyNzE1NTA0NDI0MTYyMDg1ODM3MzUxODAwNTkxMTIyMjI4NzIwNTk4MDcyOTQxMDEyMjg4MDI2OTc2NjkxNjU5MjA5MzEyNjUxMjYyNDg5NTEyMjM3NTEwNDM2ODU1NTUyMzYxODEwMDk0MDA4ODE5NDg4NDA2MTg3MTcwMzY1NDM2MDIzNTMwOTQ3OTQ2NjI4OTYwOTM3NTYzMzYxNzI3MzUwNDIzMjcyOTU3ODI2MzQwNjYyMDIxNDgwMjg2MzUzNTA1MDgzMDg3MTU4NjMyMzY5MDY3NDI4Mjc0NTEyNjg1NjU0MDk0OTE1NTE4NjAzNzk5MzE4MjUyOTcxNzExNDI1MTEyMzIzNzc3NTQ0ODE3Njg0MzYyNjEyMjM2Mjk2MjQzMjI1NzE1MDI1ODU3MDA2NjEyNjk5NDYyNjUzMzgzNzE4MjUxMTMjNTk2ODcyMDIzMzEwMDU2MTgzMjU2MDczNDI0MDg1NDUxMDk0NDYwNDIzMzI2MTEwMDY4Njg4Njc5MjMxNjg0NTA1OTgxOTUwNTQyMTI5MDU4NTgxMzc2NDU0NjIyODA1Nzg1NjcwNTgxMzEzODk5MjYyNDk0MDI1NzMzMDk3MDIzMTIyNzE5MzMyNTI1NDU4NjI2NTUxNzQxODg0OTkyOTY1OTExNzQ1MzI2NzEzMzY1NjU3Mzc4MDg3MDAwNDQ4NTExNTk1NjY0MjE3MDcyODQ4NjczNzM3MDAwMDg0Mjg1NTc2NzE0MTEzOTM5ODU4MDQwMDgwODA5NDc0OTk5NTYwNTYzNDYwOTAzODgzNzUyNjkyOTQ5NjMyMTI2NTc1MDIzMzU0NzU2MzU4MTYyNTgxNTM2MDUyMzQ3NTU2NTkxNDA4NjAzMjAyMDIxODUxMzkxMDUzOTkzOTE2MzkyODc3MjAyNzc4NjM2NDUxMDYyNDEwNjA5NTkwNjYzNDQ3ODQ2ODczODc2NjQ5OTYwNzUwNTA0OTM1NTAwNjMwODg3MjUwODkyNTU5MjE3NjcyNDIwNDQ4NDQ4NjM2MjgwNzc0Mjc4NTk0NjA1MjEzMDQxNTUxNzcyNTYyMDg2ODQ0NjYyMjM4MjUwNjI3NzAwMzM4NDE3ODU5NDc4NDMwNzY4NzkwNTY1NDg0MDUzNTc5MjY5MDA0NTg4MjE1MTA3MTQ0ODI5MDg0OTIwMTY0NjIyNDU3NDUwODk1NDg5OTE1NDAzNDEyMjYwMDMwMzUyODIzOTczMzQ1MTkxMTEwMzk1ODM3NjYzNTY4MDc1ODgyNzk1MTg4ODkwODA0ODEzNDM4MzQyOTY3MTkjY29sZGNoaXByc2E=</textarea><br/>
<label for="ta">Text to encrypt/decrypt </label>
<textarea id="ta"></textarea><br/>
<button class="en" onClick="encrypt()" id="en">Encrypt</button>
<br />
<button class="en" onClick="decrypt()" id="de">Decrypt</button>
<br />
<button class="en" onClick="gen()">Generate new keys</button>
<!--<button class="en" onClick="show()">Show Public Key</button>-->
<p>Encryption is the process of turning messages or information in such a way that the message can't be read unless its decrypted with the decryption key. The decryption key must be kept secret to prevent hackers to view the encrypted data.
</p>
</div>



</body>
</html>