<?php
namespace phpbob\analyze;

class ClosureParam implements CallParam {

	private $closureString;
	
	public function __construct($closureString) {
		$this->closureString = $closureString;
	}
	
	public function append($string) {
		$this->closureString .= $string;
	}
	
	public function __toString() {
		return $this->closureString;
	}
}