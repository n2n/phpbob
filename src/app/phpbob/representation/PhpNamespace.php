<?php
namespace phpbob\representation;

use phpbob\PhpKeyword;

class PhpNamespace {
	use PrependingCodeTrait;
	
	private $namespace;
	
	public function __construct($namespace = null, $prependingCode = null) {
		$this->namespace = $namespace;
		$this->prependingCode = $prependingCode;
	}
	
	public function getNamespace() {
		return $this->namespace;
	}

	public function setNamespace($namespace) {
		$this->namespace = $namespace;
	}

	public function __toString() {
		if (null === $this->namespace) return '';
		
		return $this->getPrependingString() . PhpKeyword::KEYWORD_NAMESPACE . ' ' . $this->namespace 
				. PhpKeyword::SINGLE_STATEMENT_STOP;
	}
}