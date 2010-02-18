<?php
	require_once 'dbc.php';
	require_once 'charconv.php';
	require_once 'magnet.php';
	$page = intval($_GET['page']);
	if($page <= 0)
		$page = 1;
	$gwords = array();
	$where = 'WHERE size > 0';
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
			$team = '';
		$where .= $searchcondition . $team;
	}
	$perpage = 20;
	$start = ($page - 1) * $perpage;
	//echo $where;
	$SQL = 'SELECT * FROM pr_items ' . $where . ' ORDER BY date DESC LIMIT ' . $start . ',' . $perpage;
	$CSQL = 'SELECT count(*) FROM pr_items ' . $where;
	header("Content-Type: application/atom+xml; charset=UTF-8");
	$torrents = $db->GetAll($SQL);
	$tcount = ceil($db->GetOne($CSQL) / $perpage);
	$uri = $_SERVER['REQUEST_URI'];
	//var_dump($torrents);
	echo '<?xml version="1.0" encoding="UTF-8"?>
	<feed xmlns="http://www.w3.org/2005/Atom">
	<link href="http://popro.info' . $uri . '" rel="self" type="application/atom+xml" />
	<id>http://popro.info/feed.php</id>
	<title>破肉図書館</title>
	<subtitle>肉之书于肉之枢，得之手于得之首</subtitle>
	<link href="http://popro.info/" />
	<updated>'.date('Y-m-d\TH:i:sP').'</updated>
';
	foreach($torrents as $tor)
	{
		extract($tor);
		$title = htmlspecialchars($title);
		$title = str_replace('amp;amp;', 'amp;', $title);
		$dtime = date('Y-m-d H:i:s', $date);
		$feedtime = date('Y-m-d\TH:i:sP', $date);
		$sec = time() - $date;
		/*
		$ftime = ($sec > 86400) ? sprintf('%.1f 天前', $sec / 86400) :
			(($sec > 3600) ? sprintf('%.1f 小时前', $sec / 3600) :
			sprintf('%.1f 分钟前', $sec / 60));
		*/
		$fsize = ($size > 1048576) ? sprintf('%.2f GB', $size / 1048576) :
				(($size > 1024) ? sprintf('%.2f MB', $size / 1024) :
				sprintf('%d KB', $size));
		$dsize = number_format($size);
		$stitle = $title;
		if(isset($gwords))
		{
			foreach($gwords as $w)
			{
				$stitle = str_ireplace($w, '<span style="color: #996666 !important;">' . $w . '</span>', $stitle);
				$w = cht($w);
				$stitle = str_ireplace($w, '<span style="color: #996666 !important;">' . $w . '</span>', $stitle);
			}
		}
		$stitle = str_ireplace($teamword, '<span style="color: #22AA00 !important;">' . $teamword . '</span>', $stitle);
		if(empty($team))
			$team = '个人发布';
		$surl = '';
		$stemplate = '来自 <cite>%s</cite>';
		switch($source)
		{
			case 'dmhy':
				//$surl = 'http://share.dmhy.org/topics/view/hash_id/' . $hash;
				$surlstr = sprintf($stemplate, 'share.dmhy.org &gt; ' . $hash);
				break;
			case 'ktxp':
				//$surl = 'http://bt.ktxp.com/search.php?keyword=' . $hash;
				$surlstr = sprintf($stemplate, 'bt.ktxp.com &gt; ' . $hash);
				break;
			default:
				$surlstr = '';
				break;
		}
		echo <<<EOT
	<entry>
		<id>http://popro.info/dtor.php?hash=$hash</id>
		<title type="html">$title</title>
		<link href="http://popro.info/dtor.php?hash=$hash" />
		<updated>$feedtime</updated>
		<author>
			<name>$team</name>
		</author>
		<content type="xhtml" xml:lang="zh-CN" xml:base="http://popro.info/">
			<div xmlns="http://www.w3.org/1999/xhtml">
				$stitle ($fsize)<br />
				于 $dtime<br /><br />
				$surlstr
			</div>
		</content>
	</entry>

EOT;

	}
	echo '</feed>';