<?php
	require_once 'dbc.php';
	require_once 'bencoding.inc.php';
	
	$ReadSQL = 'SELECT date,hash FROM pr_items LEFT JOIN pr_info USING(hash) WHERE size = 0 LIMIT 500';
	$WriteDBSQL = 'INSERT INTO pr_info (hash, infoblock, files) VALUES (?, ?, ?)';
	$WriteItemSQL = 'UPDATE pr_items SET size = ? WHERE hash = ?';
	$mysqli = &$db->_connectionID;
	
	$lines = $db->GetAll($ReadSQL);
	//$lines = array(array('hash' => 'a94e22864f5bfeb525df24a0955cc39a78d91cd4'));
	$i = 0;
	foreach($lines as $line)
	{
		$hash = $line['hash'];
		//echo $hash, "\n";
		echo $i++ . "<br />\r";
		$fn = 't/' . $hash{0} . '/' . $hash . '.torrent';
		//$fnp = $fn . '.php';
		if(!file_exists($fn))
		{
			$torr = gettorrent($line['date'], $hash);
			file_put_contents($fn, $torr);
			//continue;
		}
		$data = BEncoding::decode($fn, false);
		if(!is_object($data) || $data->broken)
		{
			echo "$hash broken!<br />\n";
			$torr = gettorrent($line['date'], $hash);
			if(strpos($torr, 'ui-state-error') !== false)
			{
				// delete file info
				$db->Execute('DELETE FROM pr_items WHERE hash = ?', array($hash));
				$db->Execute('DELETE FROM pr_info WHERE hash = ?', array($hash));
			}
			else
				file_put_contents($fn, $torr);
			continue;
		}
		$infoblock = &$data->tree['info'];
		$files = array();
		$haspadding = false;
		$len = 0;
		if(isset($infoblock['files']))
		{
			foreach($infoblock['files'] as $fi)
			{
				$nfi = array();
				if(isset($fi['path.utf-8']))
					$filename = join('/', $fi['path.utf-8']);
				else
					$filename = join('/', $fi['path']);
				if(strpos($filename, '__padding_') !== false)
				{
					$haspadding = true;
					continue;
				}
				$nfi['name'] = $filename;
				$nfi['length'] = $fi['length'];
				$len += $fi['length'] / 1024;
				if(isset($fi['ed2k']))
					$nfi['ed2k'] = bin2hex($fi['ed2k']);
				if(isset($fi['filehash']))
					$nfi['filehash'] = bin2hex($fi['filehash']);
				$files[] = $nfi;
			}
		}
		else
		{
			$fi = $infoblock;
			$nfi = array();
			if(isset($fi['name.utf-8']))
				$filename = $fi['name.utf-8'];
			else
				$filename = $fi['name'];
			$nfi['name'] = $filename;
			$nfi['length'] = $fi['length'];
			$len += $fi['length'] / 1024;
			if(isset($fi['ed2k']))
				$nfi['ed2k'] = bin2hex($fi['ed2k']);
			if(isset($fi['filehash']))
				$nfi['filehash'] = bin2hex($fi['filehash']);
			$files[] = $nfi;
		}
		$WriteDB = $mysqli->prepare($WriteDBSQL);
		$WriteDB->bind_param('sbb', $hash, $x1, $x2);
		$WriteDB->send_long_data(1, serialize($infoblock));
		$WriteDB->send_long_data(2, serialize($files));
		$WriteDB->execute();
		//$files = array(
		//$db->Execute($WriteDBSQL, array($hash, serialize($infoblock), serialize($files)));
		$WriteDB->close();
		$db->Execute($WriteItemSQL, array($len, $hash));
		//var_dump($files);
	}
