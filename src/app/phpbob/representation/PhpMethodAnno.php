<?php
namespace phpbob\representation;

class PhpMethodAnno extends PhpAnno {
	private $methodName;
	
	public function __construct($methodName, array $annoParams = null, $prependingCode = null) {
		$this->methodName = $methodName;
		parent::__construct($annoParams, $prependingCode);
	}
	
	public function getMethodName() {
		return $this->methodName;
	}

	public function setMethodName($methodName) {
		$this->methodName = $methodName;
	}
}