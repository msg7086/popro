<?php
	ob_start();
	require_once 'dbc.php';
	require_once 'charconv.php';
	require_once 'magnet.php';
	$page = intval($_GET['page']);
	if($page <= 0)
		$page = 1;
	$gwords = array();
	$where = 'WHERE size > 0';
	$teamword = '';
	if(isset($_GET['word']))
	{
		//$word = $_GET['word'];
		$word = urldecode(urldecode(base64_decode($_GET['word'])));
		$word = str_replace('%', '', $word);
		$word = str_replace("'", '', $word);
		$word = chs($word);
		$teamword = strstr($word, '@');
		if($teamword !== false)
		{
			$word = substr($word, 0, -strlen($teamword));
			$teamword = substr($teamword, 1);
		}
		if($word)
		{
			$orwords = explode(',', $word);
			$whereor = array();
			foreach($orwords as $orword)
			{
				$wherearr = array();
				$words = explode(' ', $orword);
				foreach($words as $w)
				{
					$wherearr[]= " stitle LIKE '%$w%'\n";
					$gwords[] = $w;
				}
				$whereor[] = join(' AND ', $wherearr);
			}
			$searchcondition = ' AND (' . join(' OR ', $whereor) . ")\n";
		}
		else
			$searchcondition = '';
		if($teamword !== false)
			$team = " AND (stitle LIKE '%$teamword%' OR team LIKE '%$teamword%')\n";
		else
		{
			$team = '';
			$teamword = '';
		}
		$where .= $searchcondition . $team;
	}
	$perpage = 40;
	$start = ($page - 1) * $perpage;
	//echo $where;
	$SQL = 'SELECT * FROM pr_items ' . $where . ' ORDER BY date DESC LIMIT ' . $start . ',' . $perpage;
	$CSQL = 'SELECT count(*) FROM pr_items ' . $where;
	$torrents = $db->GetAll($SQL);
	$tcount = ceil($db->GetOne($CSQL) / $perpage);
	//var_dump($torrents);
	echo '<tbody count="' . $tcount . '">
	<colgroup>
		<col width="*" />
		<col width="20%" />
		<col width="20%" />
	</colgroup>';
	if($tcount == 0)
	{
		echo <<<EOT
	<tr class="main-index-row main-index-row-light main-index-row-en  ">
		<td colspan="3">
			No data.
		</td>
	</tr>

EOT;
	}
	$lastday = '';
	foreach($torrents as $tor)
	{
		extract($tor);
		$day = date('Y-m-d H:00', $date);
		if($day != $lastday && $lastday != '')
		{
			echo <<<EOT
	<tr class="main-index-row main-index-row-dark main-index-row-en  ">
		<td colspan="3" style="text-align:center">
			$lastday
		</td>
	</tr>

EOT;
		}
		$lastday = $day;
		$dtime = date('Y-m-d H:i:s', $date);
		$sec = time() - $date;
		$ftime = ($sec > 86400) ? sprintf('%.1f 天前', $sec / 86400) :
			(($sec > 3600) ? sprintf('%.1f 小时前', $sec / 3600) :
			sprintf('%.1f 分钟前', $sec / 60));
		$fsize = ($size > 1048576) ? sprintf('%.2f GB', $size / 1048576) :
				(($size > 1024) ? sprintf('%.2f MB', $size / 1024) :
				sprintf('%d KB', $size));
		$dsize = number_format($size);
		$stitle = $title;
		if(isset($gwords))
		{
			foreach($gwords as $w)
			{
				$stitle = str_ireplace($w, '<span class="kw-light">' . $w . '</span>', $stitle);
				$w = cht($w);
				$stitle = str_ireplace($w, '<span class="kw-light">' . $w . '</span>', $stitle);
			}
		}
		if($teamword)
			$stitle = str_ireplace($teamword, '<span class="kw-team">' . $teamword . '</span>', $stitle);
		if($team)
		{
			$fteam = '<span class="team">' . $team . '</span>';
			$fteam = str_ireplace($teamword, '<span class="kw-team">' . $teamword . '</span>', $fteam);
			$fteam = '<a href="#" onclick="PreSearch(\'@' . $team . '\');return false;">'  . $fteam . '</a>';
		}
		else
			$fteam = '';
		$surl = '';
		$stemplate = '<br /><cite>%s - </cite>
<span class="gl">
<a href="%s" target="_blank" rel="external">原始来源</a></span>';
		switch($source)
		{
			case 'dmhy':
				$surl = 'http://share.dmhy.org/topics/view/hash_id/' . $hash;
				$surlstr = sprintf($stemplate, 'share.dmhy.org &gt; ' . $hash, $surl);
				break;
			case 'ktxp':
				$surl = 'http://bt.ktxp.com/search.php?keyword=' . $hash;
				$surlstr = sprintf($stemplate, 'bt.ktxp.com &gt; ' . $hash, $surl);
				break;
			default:
				$surlstr = '';
				break;
		}
		echo <<<EOT
	<tr class="main-index-row main-index-row-light main-index-row-en  ">
		<td class="main-index-info-cell">
			<div class="main-index-name">
				$fteam
				<a href="view.htm#$hash" title="View information about $title" target="_blank">$stitle</a>
				<!-- Source -->
				$surlstr
			</div>
		</td>
		<td class="main-index-other">
			Size: <abbr title="$dsize KB">$fsize</abbr> |
			Time: <abbr title="$dtime">$ftime</abbr>
		</td>
		<td class="main-index-link-cell ">
			<a class="magnet-link" href="magnet:?xt=urn:btih:$hash&dn=Popro - $hash$trackercode" title="Download $title using a magnet link">
			<img src="magnet.png" alt="Magnet" />
			<img src="magnet_txt.gif" alt="Magnet" /></a>
			<a class="torrent-link" href="dtor.php?hash=$hash" title="Download $title using a torrent file">
			<img src="utorrent.png" alt="Torrent" />
			<img src="utorrent_txt.gif" alt="Torrent" /></a>
		</td>
	</tr>

EOT;

	}
	echo '</tbody>';
	//&amp;tr=http%3A%2F%2Fnyaatorrents.info%3A3277%2Fannounce&amp;as=http%3A%2F%2Fwww.nyaatorrents.org%2F%3Fpage%3Ddownload%26tid%3D108113
	$c = ob_get_contents();
	//file_put_contents('t.txt', $c);
	ob_end_clean();
	if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)
	{
		header('Content-Encoding: gzip');
		ob_start('ob_gzhandler');
	}
	echo base64_encode($c);
	