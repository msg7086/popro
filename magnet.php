<?php
$tkarr = array(
	'http://share.xdmhy.net:8000/announce',
	'udp://share.xdmhy.net:8000/announce',
	'http://tracker.dmhy.org:8000/announce',
	'udp://tracker.dmhy.org:8000/announce',
	'http://tracker.ktxp.com:6868/announce',
	'udp://tracker.ktxp.com:7070/announce',
	'http://tracker.openbittorrent.com/announce',
	'http://denis.stalker.h3q.com:6969/announce',
	'http://tracker.prq.to/announce',
);
$trackercode = '';
$i = 1;
foreach($tkarr as $tk)
{
	$trackercode .= '&tr.' . $i++ . '=' . urlencode($tk);
}
