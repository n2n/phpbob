<?php
namespace phpbob\analyze;

class PhpAnnoDef {
	private $typeName;
	private $constructorParams = [];
	
	public function __construct($typeName, array $constructorParams) {
		$this->typeName = $typeName;
		$this->constructorParams = $constructorParams;
	}
	
	public function getTypeName() {
		return $this->typeName;
	}

	public function setTypeName($typeName) {
		$this->typeName = $typeName;
	}

	public function getConstructorParams() {
		return $this->constructorParams;
	}

	public function setConstructorParams($constructorParams) {
		$this->constructorParams = $constructorParams;
	}
}