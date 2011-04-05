<?php
/*
 * BigInt.php
 * 
 * Class that helps PHP render and increment large intergers without
 * overflowing.
 *   
 * Litter is coded by Scott VonSchilling. He needs a job. If you like
 * what you see, please email scottvonschilling [at] gmail [dot] com.
 * 
 */

class BigInt{
	private $array;
	private $DEFAULT_SPLIT = 10;
	
	function __construct($num, $split = -1){
		if ($split == -1) $split = $this->DEFAULT_SPLIT;
		$this->array = str_split($num, $split);
	}
	
	function __toString(){
		return $this->getNum();
	}
	
	public function increment($n = 1){
		$l = count($this->array) - 1;
		$m = $this->array[$l];
		$leadingZeros = 0;
		while ($m[0] == '0'){
			$leadingZeros++;
			$m = substr($m, 1);
		}
		$m += $n;
		$this->array[$l] = '';
		for ($i = 0; $i < $leadingZeros; $i++)
			$this->array[$l] .= '0';
		$this->array[$l] .= $m;
	}
	
	public function getNum(){
		$s = '';
		for ($i = 0; $i < count($this->array); $i++){
			$s .= $this->array[$i];
		}
		return $s;
	}
	

}