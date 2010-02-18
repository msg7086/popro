<?php
	/*
	BEncoding Library
	
	Author: MeteorRain
	
	Date: 2008.8.29
	
	*/

class TorrentInfo
{
	var $broken = false;
	var $tree = array();
	var $hash = '';
}

class BEncoding
{
	var $_version = 1.0;
	
	public function decode($filename, $piecehash = false)
	{
		if(!file_exists($filename))
			return null;
		include_once 'bencoding.decoder.php';
		$decoder = new BDecoder(file_get_contents($filename, FILE_BINARY));
		return $decoder->parse($piecehash);
	}
	
	public function encode($tree)
	{
		include_once 'bencoding.encoder.php';
		$encoder = new BEncoder();
		return $encoder->Encode($tree);
	}
}
