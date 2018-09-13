<?php
namespace phpbob\representation\anno;

class PhpAnnoParam {
	private $phpAnno;
	private $value;
	
	public function __construct(PhpAnno $phpAnno, string $value) {
		$this->phpAnno = $phpAnno;
		$this->value = $value;
	}
	
	public function getPhpAnno() {
		return $this->phpAnno;
	}
	
	public function isString() {
		return preg_match('/(^\'.*\'$)|(^".*"$)/', $this->value);
	}
	
	public function getStringValue() {
		if (!$this->isString()) return null;
		
		return preg_replace('/((^\')|(^")|(\'$)|("$))/', '', $this->value);
	}
	
	public function __toString() {
		return $this->value;
	}
}