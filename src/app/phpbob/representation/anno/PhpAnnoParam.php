<?php
namespace phpbob\representation\anno;

use phpbob\Phpbob;

class PhpAnnoParam {
	private $phpAnnoCollection;
	private $value;
	
	public function __construct(PhpAnnoCollection $phpAnnoCollection, string $value) {
		$this->phpAnnoCollection = $phpAnnoCollection;
		$this->value = $value;
	}
	
	public function getPhpAnnoCollection() {
		return $this->phpAnnoCollection;
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