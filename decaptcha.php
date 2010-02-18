<?php
function ocrIt($str)
{
	$keys = array (
		5 => 'xxxxx--x--------------xxxxx--xx-------------x---x---xx------------x--x-----x------------x--x-----x------------x--xx---xx------------x---xxxxx------------------xxx',
		7 => 'x-------xx------------x------xxx------------x-----xx--------------x----xx---------------x---xx----------------x--xx-----------------xxxx------------------xxx',
		4 => 'xx-------------------xxx------------------xx-x-----------------xx--x----------------xx---x---------------xxxxxxxxxx------------xxxxxxxxxx------------------x',
		'x' => 'x-----x---------------xx---xx----------------xx-xx------------------xxx-------------------xxx------------------xx-xx----------------xx---xx---------------x-----x',
		'y' => 'xxxxx--x--------------xxxxxx--x------------------xx-x-------------------x-x-------------------x-x------------------x--x-------------xxxxxxxxx-------------xxxxxxxx',
		9 => 'xx-------------------xxxx--xx-------------xx--xx--xx------------x----x---x------------x----x---x------------xx--x---xx-------------xxxxxxxx---------------xxxxxx',
		6 => 'xxxxxx---------------xxxxxxxx-------------xx---x--xx------------x---x----x------------x---x----x------------xx--xx--xx-------------xx--xxxx-------------------xx',
		'a' => 'xx-----------------x-xxxx---------------xx-x--x---------------x--x--x---------------x--x--x---------------x--x-x----------------xxxxxxx----------------xxxxxx',
		'p' => 'xxxxxxxxx-------------xxxxxxxxx--------------x---x----------------x-----x---------------x-----x---------------xx---xx----------------xxxxx------------------xxx',
		'w' => 'xxxxxx----------------xxxxxxx--------------------xx-----------------xxxx------------------xxxx---------------------xx---------------xxxxxxx---------------xxxxxx',
		'c' => 'xxx------------------xxxxx----------------xx---xx---------------x-----x---------------x-----x---------------x-----x---------------xx---xx----------------x---x',
		'n' => 'xxxxxxx---------------xxxxxxx----------------x--------------------x---------------------x---------------------xx---------------------xxxxxx-----------------xxxxx',
		'h' => 'xxxxxxxxxx------------xxxxxxxxxx----------------x--------------------x---------------------x---------------------xx---------------------xxxxxx-----------------xxxxx',
		'j' => 'xx--------------------xxx---------------------x---------------------x-------------x-------x----------xx-xxxxxxxxx----------xx-xxxxxxxx',
		's' => 'xx--x----------------xxxx-xx---------------x--x--x---------------x--x--x---------------x--x--x---------------x--x--x---------------xx-xxxx----------------x--xx',
		'f' => 'x---------------------x-----------------xxxxxxxxx------------xxxxxxxxxx------------x----x----------------x----x----------------xxx--------------------xx',
		't' => 'x---------------------x-------------------xxxxxxxx--------------xxxxxxxxx---------------x-----x---------------x-----x--------------------xx--------------------x',
		'e' => 'xxx------------------xxxxx----------------xx-x-xx---------------x--x--x---------------x--x--x---------------xx-x--x----------------xxx-xx-----------------xx-x',
		'k' => 'xxxxxxxxxx------------xxxxxxxxxx-----------------xx-------------------xxxx-----------------xx--xx----------------x----xx---------------------x',
		'm' => 'xxxxxxx----------------xxxxxx---------------x---------------------xxxxxxx----------------xxxxxx---------------x---------------------xxxxxxx----------------xxxxxx',
		'r' => 'x---------------------xxxxxxx----------------xxxxxx---------------xx--------------------x---------------------x---------------------xx---------------------x',
	);
	
	echo "Start analysing...\n";
	$img = imagecreatefromstring($str);
	$w = imagesx($img);
	$h = imagesy($img);
	
	$stdc = imagecolorat($img, 1, 1);
	$lastc = -1;
	$prsvy = 13;
	for($i = 0; $i < $h; $i++)
	{
		$cs = array(
			imagecolorat($img, 2, $i),
			imagecolorat($img, 12, $i),
			imagecolorat($img, 13, $i),
			);
		sort($cs);
		$c = $cs[1];
		/*
		if($c == $lastc)
		{
			// we see on word; reduce $prsvy
			$prsvy--;
		}
		*/
		for($j = 0; $j < $w; $j++)
			if($j < 1 || $j >= $w-1 || imagecolorat($img, $j, $i) == $c)
				imagesetpixel($img, $j, $i, $stdc);
	}
	
	//$r = imagepng($img, 'train_' . $fn . '.png', 0);
	
	$serial = '';
	for($i = 0; $i < $w; $i++)
		for($j = 0; $j < $h; $j++)
		{
			$c = imagecolorat($img, $i, $j);
			$serial .= ($c != $stdc ? 'x' : '-');
		}
	
	//$serials = str_split($serial, intval(strlen($serial) / 5));
	//$serials = explode(str_repeat('-', 50), $serial);
	$serials = preg_split('!-{40,80}!', $serial, 0, PREG_SPLIT_NO_EMPTY);
	if(count($serials) < 5)
	{
		echo "missing struct.\n";
		return false;
	}
	$ocr = '';
	for($i = 0; $i < 5; $i++)
	{
		$ch = '';
		foreach($keys as $char => $code)
			if(strpos($code, trim($serials[$i], '-')) !== false)
			{
				$ch = $char;
				break;
			}
		if(empty($ch)) $ch = '?';
		$ocr .= $ch;
	}
	echo "Ocr result: $ocr.\n";
	return $ocr;
}


/*
	$fns = explode("\n", '57547
5xy96
66a6y
79ypw
9c5nh
cc966
cjsf4
ctn7x
h95ft
hej46
j4p9j
n66jk
pjmr9
tjwmj
wyryx
xwmnw
yfphs');
*/
	/*
	$count = 3;
	while($count--)
	{
		//$fn = trim($fn);
		//echo "$fn.png\n";
		//$img = imagecreatefrompng('train/' . $fn . '.png');
		$fn = $count;
		echo "$fn.png\n";
		$img = imagecreatefrompng('http://share.dmhy.org/common/generate-captcha');
	}
	*/
	
	//var_export($keys);
	
	