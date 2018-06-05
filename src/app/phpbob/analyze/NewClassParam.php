<?php
namespace phpbob\analyze;

use phpbob\Phpbob;

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
		return Phpbob::KEYWORD_NEW . ' ' . $this->typeName . 
				Phpbob::PARAMETER_GROUP_START . implode(Phpbob::PARAMETER_SEPERATOR . ' ',  
						$this->constructorParams) . Phpbob::PARAMETER_GROUP_END; 
	}
}