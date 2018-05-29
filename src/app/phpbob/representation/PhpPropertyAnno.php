<?php
namespace phpbob\representation;

class PhpPropertyAnno extends PhpAnno {
	
	private $propertyName;
	
	public function __construct($propertyName, array $annoParams = null, $prependingCode = null) {
		$this->propertyName = $propertyName;
		parent::__construct($annoParams, $prependingCode);
	}
	
	public function setPropertyName($propertyName) {
		$this->propertyName = $propertyName;
	}
	
	public function getPropertyName(): string {
		return $this->propertyName;
	}
}