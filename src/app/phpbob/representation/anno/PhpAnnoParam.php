<?php
namespace phpbob\representation\anno;

use phpbob\Phpbob;

class PhpAnnoParam {
	private $phpAnno;
	private $value;
	
	public function __construct(PhpAnno $phpAnno, string $value) {
		$this->phpAnno = $phpAnno;
		$this->value = $value;
	}
	
	public function getPhpAnnoCollection() {
		return $this->phpAnno;
	}
	
	public function isString() {
		return preg_match('/(^\'.*\'$)|(^".*"$)/', $this->value);
	}
	
	public function getStringValue() {
		if (!$this->isString()) return null;
		
		return preg_replace('/((^\')|(^")|(\'$)|("$))/', $this->value);
	}
	
	public function __toString() {
		return $this->value;
	}
}