<?php
	require_once 'dbc.php';
	require_once 'bencoding.inc.php';
	$hash = $_GET['hash'];
	if(!preg_match('!^[0-9a-f]{40,40}$!i', $hash))
		die('Unknown hash.');
	if(!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'popro.info/') === false)
	{
		header('Location: http://popro.info/view.htm#' . $hash);
		die;
	}
	$infoblock = $db->GetOne('SELECT infoblock FROM pr_info WHERE hash = ?', array($hash));
	if(!$infoblock)
	{
		header('Location: http://popro.info/view.htm#' . $hash);
		die;
	}
	$infoblock = unserialize($infoblock);
	$tree = array(
		'announce' => 'http://share.xdmhy.net:8000/announce',
		'announce-list' => array (
			array (
				'http://share.xdmhy.net:8000/announce',
				'udp://share.xdmhy.net:8000/announce',
			),
			array (
				'http://tracker.dmhy.org:8000/announce',
				'udp://tracker.dmhy.org:8000/announce',
			),
			array (
				'http://tracker.ktxp.com:6868/announce',
				'http://tracker.ktxp.com:7070/announce',
				'udp://tracker.ktxp.com:6868/announce',
				'udp://tracker.ktxp.com:7070/announce',
			),
			array (
				'http://tracker.openbittorrent.com/announce',
				'udp://tracker.openbittorrent.com:80/announce',
			),
			array (
				'http://denis.stalker.h3q.com:6969/announce',
			),
			array (
				'http://tracker.prq.to/announce',
			)
		),
		'created by' => 'http://popro.info/',
		'creation date' => time(),
		'info' => $infoblock
	);
	$data = BEncoding::encode($tree);
	header('Content-type: application/x-bittorrent');
	header('Content-Disposition: attachment; filename="' . $hash . '.torrent"');
	echo $data;
	