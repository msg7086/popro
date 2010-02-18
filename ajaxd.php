<?php
	ob_start();
	require_once 'dbc.php';
	require_once 'magnet.php';
	$hash = $_GET['hash'];
	if(!preg_match('!^[0-9a-f]{40,40}$!i', $hash))
		die('Unknown hash.');
	
	$SQL = 'SELECT pr_items.*, pr_info.files FROM pr_items LEFT JOIN pr_info USING (hash) WHERE hash = ?';
	$tor = $db->GetRow($SQL, $hash);
	if(!$tor)
		die('Torrent not found.');
	extract($tor);
	$dtime = date('Y-m-d H:i:s', $date);
	$sec = time() - $date;
	$ftime = ($sec > 86400) ? sprintf('%.1f 天前', $sec / 86400) :
			(($sec > 3600) ? sprintf('%.1f 小时前', $sec / 3600) :
			sprintf('%.1f 分钟前', $sec / 60));
	$fsize = ($size > 1048576) ? sprintf('%.2f GB', $size / 1048576) :
			(($size > 1024) ? sprintf('%.2f MB', $size / 1024) :
			sprintf('%d KB', $size));
	$dsize = number_format($size);
	if($team)
		$team = '<span class="team">' . $team . '</span>';
	$files = unserialize($files);
	//var_dump($files);
	$filesblock = '<table class="torrent-stats" width="100%">
	<tr>
		<th class="torrent-details-label" width="*">文件名</th>
		<th class="torrent-details-label" width="13%">大小</th>
		<th class="torrent-details-label" width="13%">其它信息</th>
	</tr>';
	foreach($files as $file)
	{
		extract($file);
		$filesize = ($length > 1048576) ? sprintf('%.2f MB', $length / 1048576) : sprintf('%.2f KB', $length / 1024);
		$filesblock .= '<tr><td>' . $name . '</td><td class="tar">' . $filesize . '</td><td class="tac">';
		$sname = strstr($name, '/');
		if(isset($ed2k) && $length >= 65536)
		{
			if($sname)
				$sname = substr($sname, 1);
			else
				$sname = $name;
			$name = rawurlencode($name);
			$filesblock .= "<a href=\"ed2k://|file|$name|$length|$ed2k/\">
		<img src=\"emule.png\" alt=\"eMule ED2k Link\" />
			ED2k</a>";
		}
		$filesblock .= '</td></tr>';
	}
	$filesblock .= '</table>';
	echo <<<EOT
	<div class="torrent-details-row-light">
		<div class="torrent-details-label">Database ID</div>
		<div class="torrent-details-content">$itemid</div>
	</div>
	<div class="torrent-details-row-dark">
		<div class="torrent-details-label">Submission Time</div>
		<div class="torrent-details-content">$dtime ($ftime)</div>
	</div>
	<div class="torrent-details-row-light">
		<div class="torrent-details-label">File/Folder Name</div>
		<div class="torrent-details-content">
		$filesblock
		</div>
	</div>
	<div class="torrent-details-row-dark">
		<div class="torrent-details-label">Size</div>
		<div class="torrent-details-content">$fsize ($dsize KB)</div>
	</div>
	<div class="torrent-details-row-light">
		<div class="torrent-details-label">Info Hash</div>
		<div class="torrent-details-content"><span class="hash">$hash</span></div>
	</div>
	<div class="torrent-details-row-dark">
		<table class="torrent-stats">
			<tr>
				<th class="torrent-details-label">Seeds</th><th class="torrent-details-label">Leechers</th><th class="torrent-details-label">Completed</th>
			</tr>
			<tr>
				<td>?</td><td>?</td><td>?</td>
			</tr>
		</table>
	</div>
	<div class="torrent-details-row-light">
		<a href="magnet:?xt=urn:btih:$hash&dn=Popro - $hash$trackercode" class="details-link">
		<img src="magnet.png" alt="Magnet" />
		磁链下载 Magnet Link
		</a>
	</div>
	<div class="torrent-details-row-dark">
		<a href="dtor.php?hash=$hash" class="details-link">
		<img src="utorrent.png" alt="Torrent" />
		种子下载 Torrent Link
		</a>
	</div>

EOT;

	echo '</tbody>';
	//&amp;tr=http%3A%2F%2Fnyaatorrents.info%3A3277%2Fannounce&amp;as=http%3A%2F%2Fwww.nyaatorrents.org%2F%3Fpage%3Ddownload%26tid%3D108113
	$c = ob_get_contents();
	ob_end_clean();
	echo base64_encode($c);
	