<?php
	require_once 'dbc.php';
	$params = array();
	if(isset($_POST['post']))
	{
		$posts = json_decode(urldecode(base64_decode($_POST['post'])), true);
		if(!is_array($posts))
			die('Wrong input');
		foreach($posts as $p)
			$params[$p['name']] = $p['value'];
	}
	else
		$params = $_GET;
	
	var_dump($params);
	
	switch($Action)
	{
		case 'Login':
		{
			
		}
	}
	