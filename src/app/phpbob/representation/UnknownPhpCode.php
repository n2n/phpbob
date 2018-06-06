<?php
namespace phpbob\representation;

class UnknownPhpCode implements PhpNamespaceElement {
	private $code;
	
	public function __construct(string $code) {
		$this->code = $code;
	}
	
	public function __toString() {
		return $this->code;
	}
	
	public function getPhpTypeDefs() : array {
		return [];
	}
}