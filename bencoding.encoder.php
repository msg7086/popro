<?php
class BEncoder
{
	function __construct()
	{
	}
	
	function Encode($arr)
	{
		if(!is_array($arr))
			return '';
		$this->pieceback($arr);
		return $this->enc($arr);
	}
	
	function hex2bin($hextext)
	{
		return pack("H*", $hextext); 
	}
	
	function pieceback(&$tree) //process the SHA-1 hash to machine format
	{
		if(isset($tree['info']['pieces']))
			return;
		$data = &$tree['info']['pieces-r'];
		$tree['info']['pieces'] = '';
		for($i = 0; $i < count($data); $i++)
			$tree['info']['pieces'] .= $this->hex2bin($data[$i]);
		$tree['info']['pieces-r'] = null;
		unset($tree['info']['pieces-r']);
	}
	
	function enc($arr)
	{
		if(gettype($arr) === 'integer')
			return $this->enc_int($arr);
		elseif(gettype($arr) === 'string' && strlen($arr) >= 10 && is_numeric($arr))
			return $this->enc_intstr($arr);
		elseif(gettype($arr) === 'string')
			return $this->enc_string($arr);
		elseif(gettype($arr) === 'array')
			if(isset($arr[0]) || empty($arr))
				return $this->enc_list($arr);
			else
				return $this->enc_dic($arr);
		return 'INVALID_DATA_TYPE';
	}
	function enc_int($value)
	{
		return 'i' . intval($value) . 'e';
	}
	function enc_intstr($value)
	{
		return 'i' . $value . 'e';
	}
	function enc_string($value)
	{
		return strlen($value) . ':' . $value;
	}
	function enc_list($list)
	{
		$thistext = 'l';
		foreach($list as $item)
			$thistext .= $this->enc($item);
		$thistext .= 'e';
		return $thistext;
	}
	function enc_dic($dic)
	{
		$thistext = 'd';
		foreach($dic as $key => $item)
		{
			$thistext .= $this->enc($key);
			$thistext .= $this->enc($item);
		}
		$thistext .= 'e';
		return $thistext;
	}
}
?>
