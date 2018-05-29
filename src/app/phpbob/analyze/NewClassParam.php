<?php
namespace phpbob\analyze;

use phpbob\PhpKeyword;

class NewClassParam implements AnnoParam, CallParam {
	
	private $constructorParams = array();
	private $typeName;
	
	public function __construct($typeName) {
		$this->typeName = $typeName;
	}
	
	public function getConstructorParams() {
		return $this->constructorParams;
	}
	
	public function addConstructorParam(CallParam $constructorParam) {
		$this->constructorParams[] = $constructorParam;
	}

	public function getTypeName(): string {
		return $this->typeName;
	}

	public function __toString() {
		return PhpKeyword::KEYWORD_NEW . ' ' . $this->typeName . 
				PhpKeyword::PARAMETER_GROUP_START . implode(PhpKeyword::PARAMETER_SEPERATOR . ' ',  
						$this->constructorParams) . PhpKeyword::PARAMETER_GROUP_END; 
	}
}