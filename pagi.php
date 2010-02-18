<?php
	require 'dbc.php';
	
	$SQL = 'SELECT count(*) FROM items';
	
	$tcount = $db->GetOne($SQL);
	//var_dump($torrents);
		echo <<<EOT
	<li id="pagecount" pagecount="$tcount">Pages: </li>
EOT;
	}
	