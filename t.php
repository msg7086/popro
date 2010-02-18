<?php
	require_once 'decaptcha.php';
function strstrb($h,$n){
	return array_shift(explode($n,$h,2));
}

$cookie = '';
function getCaptcha()
{
	global $cookie;
	if(!empty($cookie))
		$cookiestr = '
Cookie: PHPSESSID=' . $cookie;
	else
		$cookiestr = '';

	$fp = fsockopen('share.dmhy.org', 80);
	if($fp)
	{
		fwrite($fp, 
'GET /common/generate-captcha?code=' . (time() - 5) . ' HTTP/1.0
Host: share.dmhy.org
User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7
Accept: image/png,image/*;q=0.8,*/*;q=0.5
Connection: close' . $cookiestr . '

');
		$data = '';
		while(!feof($fp))
		{
			$data .= fread($fp, 1024);
		}
		fclose($fp);
		$data = explode("\r\n\r\n", $data, 2);
		if(empty($cookie))
		{
			if(preg_match('!PHPSESSID=(.*);!', $data[0], $match))
				$cookie = $match[1];
			else
				echo 'No set-cookie found!';
		}
		return $data[1];
	}
	
}

function gettorrent($date, $hash)
{
	global $cookie;
	$imgstr = getCaptcha();
	$code = ocrIt($imgstr);
	if(!$code)
	{
		$imgstr = getCaptcha();
		$code = ocrIt($imgstr);
	}
	if(empty($cookie))
		die('No Cookie!');
		$cookiestr = '
Cookie: PHPSESSID=' . $cookie;
	$fp = fsockopen('share.dmhy.org', 80);
	if($fp)
	{
		fwrite($fp, 
'POST /topics/down/date/' . $date . '/hash_id/' . $hash . ' HTTP/1.0
Host: share.dmhy.org
User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7
Accept: */*
Connection: close
Content-Length: 18
Content-Type: application/x-www-form-urlencoded
Referer: http://share.dmhy.org/topics/view/hash_id/' . $hash . '
Cookie: uid=54204; rsspass=8a88f073534f35b0da52e0c007; pass=a55664b3144c400e99365fbede; PHPSESSID=' . $cookie . '

captcha_code=' . $code);
		$data = '';
		while(!feof($fp))
		{
			$data .= fread($fp, 1024);
		}
		fclose($fp);
		$data = explode("\r\n\r\n", $data, 2);
		return $data[1];
	}

}
	//echo gettorrent(1263136917, 'ee0f56a976602dab9141e8ec466b14639526d802');
	//exit;
	require_once 'dbc.php';
	require_once 'charconv.php';
	
	$CheckExist = 'SELECT count(*) FROM pr_items WHERE hash = ?';
	$WriteDBSQL = 'INSERT INTO pr_items (source, team, title, hash, date, stitle) VALUES (?, ?, ?, ?, ?, ?)';
	$pmin = 1;
	$pmax = 5;
	if(isset($_SERVER['argv'][1]))
		$pmin = $pmax = $_SERVER['argv'][1];
	if(isset($_GET['page']))
		$pmin = $pmax = intval($_GET['page']);
	for($page = $pmin; $page < $pmax + 1; $page++)
	{
	$data = file_get_contents('http://share.dmhy.org/index/index/page/' . $page);
	echo 'http://share.dmhy.org/index/index/page/' . $page . "<br />\n";
	$data = strstr($data, 'topic_list');
	$data = strstrb($data, '</table>');
	$lines = explode('<tr', $data);
	array_shift($lines);
	array_shift($lines);
	foreach($lines as $line)
	{
		$cells = explode('<td', $line);
		//  [4]=>  string(184) " nowrap="nowrap" align="center"><a class="download-arrow" title="蹇€熶笅杞? href="/topics/down/date/1260319573/hash_id/b1736af467637f063532e960ba66246a657ca950">&nbsp;</a></td>
		/*
		preg_match('!date/(\d+)/hash_id/(.*?)"!', $cells[4], $match);
		$date = $match[1];
		$hash = $match[2];
		*/
		preg_match('!(\d+).(\d+).(\d+).(\d+).(\d+)!', $cells[1], $match);
		//2009/12/29 06:17
		$date = mktime($match[4], $match[5], 0, $match[2], $match[3], $match[1]);
		preg_match('!hash_id/(.*?)"!', $cells[3], $match);
		$hash = $match[1];
	
		if(preg_match('!\t+([^<]+)</a></span>!', $cells[3], $match))
			$team = $match[1];
		else
			$team = '';
		preg_match('!\t+([^<]+)</a>\r!', $cells[3], $match);
		$title = $match[1];
		printf("\tH:%s\tD:%s\t%d<br />\n", $hash, date('Y-m-d H:i:s', $date), $date);
		$stitle = chs($title);
		$exists = $db->GetOne($CheckExist, array($hash));
		if(!$exists)
			$db->Execute($WriteDBSQL, array('dmhy', $team, $title, $hash, $date, $stitle));
		$tfn = 't/' . $hash . '.torrent';
		$tfn1 = 't/' . $hash{0} . '/' . $hash . '.torrent';
		if(file_exists($tfn1) && filesize($tfn1) == 21246)
			unlink($tfn1);
		if(!file_exists($tfn1))
		{
			if(!file_exists($tfn))
			{
				$torr = gettorrent($date, $hash);
				if(strlen($torr) == 21246)
				{
					echo "File not found error.<br />\n";
				}
				else
				{
					file_put_contents($tfn1, $torr);
					printf("%s: %d written<br />\n", $tfn1, strlen($torr));
				}
			}
			else
			{
				printf("%s -> %s<br />\n", $tfn, $tfn1);
				rename($tfn, $tfn1);
			}
		}
		//var_dump($match);
		//var_dump($cells);
		//exit;
	}
	
	}
include 'parsing.php';
