<?php
function strstrb($h,$n){
	return array_shift(explode($n,$h,2));
}

function gettorrent($date, $hash)
{
	$ddate = date('Y/m/d', $date);
	return file_get_contents("http://bt.ktxp.com/torrents/$ddate/$hash.torrent");
}
	//gettorrent(1263136917, 'ee0f56a976602dab9141e8ec466b14639526d802');
	//exit;
	require_once 'dbc.php';
	require_once 'charconv.php';
	
	$CheckExist = 'SELECT * FROM pr_items WHERE hash = ?';
	$CheckInfoExist = 'SELECT count(*) FROM pr_info WHERE hash = ?';
	$WriteDBSQL = 'INSERT INTO pr_items (source, team, title, hash, date, stitle) VALUES (?, ?, ?, ?, ?, ?)
	ON DUPLICATE KEY UPDATE source=VALUES(source), team=VALUES(team), title=VALUES(title), date=VALUES(date), stitle=VALUES(stitle);';
	$UpdateDBSQL = 'UPDATE pr_items SET source = ?, title=?, date = ?, stitle = ? WHERE hash = ?';
	$pmin = 1;
	$pmax = 1;
	if(isset($_SERVER['argv'][1]))
		$pmin = $pmax = $_SERVER['argv'][1];
	if(isset($_GET['page']))
		$pmin = $pmax = intval($_GET['page']);
	for($page = $pmin; $page < $pmax + 1; $page++)
	{
	$data = file_get_contents('http://bt.ktxp.com/sort-1-' . $page . '.html');
	echo 'http://bt.ktxp.com/sort-1-' . $page . '.html' . "<br />\n";
	$data = strstr($data, 'listTable');
	$data = strstrb($data, '</table>');
	$lines = explode('<tr', $data);
	array_shift($lines);
	array_shift($lines);
	foreach($lines as $line)
	{
		$cells = explode('<td', $line);
		//  [4]=>  string(184) " nowrap="nowrap" align="center"><a class="download-arrow" title="蹇€熶笅杞? href="/topics/down/date/1260319573/hash_id/b1736af467637f063532e960ba66246a657ca950">&nbsp;</a></td>
		
		preg_match('!/down/(\d+)/(.*?)\.torrent"!', $cells[3], $match);
		$date = $match[1];
		$hash = $match[2];
		
	
		if(preg_match('!/team-.*text_green">([^<]+)</a>!', $cells[8], $match))
			$team = $match[1];
		else
			$team = '';
		preg_match('!target="_blank">([^<]+)</a>&nbsp!', $cells[3], $match);
		$title = $match[1];
		printf("\tT:%s\tH:%s\tD:%s\t%d<br />\n", $team, $hash, date('Y-m-d H:i:s', $date), $date);
		$stitle = chs($title);
		$exists = $db->GetRow($CheckExist, array($hash));
		if(!$exists)
			$db->Execute($WriteDBSQL, array('ktxp', $team, $title, $hash, $date, $stitle));
		elseif($exists['source'] == 'dmhy')
			$db->Execute($UpdateDBSQL, array('ktxp', $title, $date, $stitle, $hash));
		continue;
		$exists = $db->GetOne($CheckInfoExist, array($hash));
		if($exists)
			continue;
		$tfn = 't/' . $hash . '.torrent';
		$tfn1 = 't/' . $hash{0} . '/' . $hash . '.torrent';
		if(!file_exists($tfn1))
		{
			if(!file_exists($tfn))
			{
				$torr = gettorrent($date, $hash);
				if(strlen($torr) == 0)
				{
					echo "zero data<br />\n";
				}
				file_put_contents($tfn1, $torr);
				printf("%s: %d written<br />\n", $tfn1, strlen($torr));
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
//include 'parsing.php';
