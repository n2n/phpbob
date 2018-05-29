<?php
namespace phpbob\analyze;

class SimpleParam implements CallParam {
	
	private $string;
	
	public function __construct($string) {
		$this->string = $string;
	}
	
	public function __toString() {
		return $this->string;
	}
}