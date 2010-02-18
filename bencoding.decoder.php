<?php

define('DEBUG', false);

class BDecoder
{
	var $parsepos = 0;
	var $cont = '';
	var $hash = '';
	var $ti = null;
	function __construct($cont)
	{
		$this->cont = &$cont;
		$this->ti = new TorrentInfo();
	}
	function parse($piecehash = false)
	{
		if($this->cont{0} != 'd')
		{
			$this->ti->broken = true;
			return;
		}
		if(DEBUG) echo '<pre>';
		$this->_parse($this->ti->tree);
		if(DEBUG) echo '</pre>';
		$this->ti->tree = &$this->ti->tree[0];
		if($piecehash)
			$this->piece();
		return $this->ti;
	}
	function piece() //process the SHA-1 hash to readable format
	{
		if(!isset($this->ti->tree['info']['pieces']))
			return 0;
		
		$pctree = array();
		$data = &$this->ti->tree['info']['pieces'];
		$d = str_split($data, 20);
		$length = count($d);
		for($i = 0; $i < $length; ++$i)
			$pctree[] = bin2hex($d[$i]);
		$this->ti->tree['info']['pieces-r'] = &$pctree;
		$this->ti->tree['info']['pieces'] = null;
		unset($this->ti->tree['info']['pieces']);
		//	20*8
		//	32*5
	}
	function _parse(&$ptree)
	{
		if($this->ti->broken)
			return 'e';
		if($this->parsepos >= strlen($this->cont))
		{
			$this->ti->broken = true;
			return 'e';
		}
		$nextch = $this->cont{$this->parsepos};
		switch($nextch)
		{
			case 'd':
				$this->parsedic($ptree);
				break ;
			case 'l':
				$this->parselist($ptree);
				break ;
			case 'i':
				$ptree[] = $this->parseint();
				break ;
			case 'e':
				$this->parsepos++;
				break ;
			default:
				if(is_numeric($nextch))
					$ptree[] = $this->parsestring();
				else
				{
					$this->ti->broken = true;
					//print_r($this->tree);
					return 'b';
				}
		}
		return $nextch;
	}
	function parsedic(&$ptree)
	{
		if(DEBUG) echo "->parsedic\n";
		$this->parsepos++;
		$thistree = array();
		while('e' != $this->_parse($thistree))
			if($thistree[count($thistree) - 1] == 'info')
				$beginpos = $this->parsepos;
			elseif(isset($beginpos) && !isset($endpos))
				$endpos = $this->parsepos;
		if(isset($endpos))
			$this->ti->hash = sha1(substr($this->cont, $beginpos, $endpos - $beginpos));
		$dictree = array();
		$len = floor(count($thistree) / 2);
		for($i = 0; $i < $len; ++$i)
			$dictree[$thistree[$i * 2]] = $thistree[$i * 2 + 1];
		$thistree = null;
		$ptree[] = &$dictree;
		if(DEBUG) echo "<-parsedic\n";
	}
	function parselist(&$ptree)
	{
		if(DEBUG) echo "->parselist\n";
		$this->parsepos++;
		$thistree = array();
		while('e' != $this->_parse($thistree));
		$ptree[] = &$thistree;
		if(DEBUG) echo "<-parselist\n";
	}
	function parsestring()
	{
		if(DEBUG) echo "->parsestring\n";
		for($i = $this->parsepos; $this->cont{$i} != ':'; ++$i);
		$length = substr($this->cont, $this->parsepos, $i - $this->parsepos);
		$data = substr($this->cont, $i + 1, $length);
		$this->parsepos = $i + 1 + $length;
		if(DEBUG) echo "<-parsestring " . (isset($data[256]) ? '[longdata]' : $data) . " \n";
		return $data;
	}
	function parseint()
	{
		if(DEBUG) echo "->parseint\n";
		$this->parsepos++;
		for($i = $this->parsepos; $this->cont{$i} != 'e'; ++$i);
		$word = substr($this->cont, $this->parsepos, $i - $this->parsepos);
		if(strlen($word) >= 10)
			$ans = $word;
		else
			$ans = intval($word);
		$this->parsepos = $i + 1;
		if(DEBUG) echo "<-parseint $ans \n";
		return $ans;
	}
}
?>
