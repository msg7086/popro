<?php
	ob_start();
	require 'dbc.php';
	
	$SQL = 'SELECT * FROM pr_keywords WHERE startdate < CURDATE() AND (enddate > DATE_ADD(CURDATE(), INTERVAL 2 WEEK) OR enddate = 0) AND hidden = 0';
	$tor = $db->GetAll($SQL);
	$datearray = array();
	foreach($tor as $t)
	{
		extract($t);
		$esearchword = rawurlencode($searchword);
		$mon = substr($startdate, 0, 7);
		if($mon{0} == '0')
			$mon = 'Longterm';
		if(!isset($datearray[$mon]))
			$datearray[$mon] = '';
		$datearray[$mon] .= <<<EOT
			<div><a class="ei" href="#$esearchword|1" onclick="return PreSearch('$searchword')">$displayname</a>
EOT;
		if($recommendteam)
		{
			$teams = explode(',', $recommendteam);
			foreach($teams as $t)
			{
				$datearray[$mon] .= <<<EOT
					<a class="team" href="#$esearchword@$t|1" onclick="return PreSearch('$searchword@$t')">$t</a>
EOT;
			}
		}
		$datearray[$mon] .= '</div>';
	}
	ksort($datearray);
	echo '<div style="clear:left;"><div>
		<a class="ei" href="#1" onclick="return PreSearch(\'\')">查看全部</a>
		</div></div>';
	foreach($datearray as $d => $v)
	{
		echo '			<br /><div style="clear:left;">' . $d . '</div>';
		echo $v;
	}
	//print_r($datearray);
	$c = ob_get_contents();
	ob_end_clean();
	echo base64_encode($c);
	