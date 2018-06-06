<?php
namespace phpbob\representation\traits;

use phpbob\representation\PhpTypeDef;

trait InterfacesTrait {
	protected $interfacePhpTypeDefs = array();
	
	public function getInterfacePhpTypeDefs() {
		return $this->interfacePhpTypeDefs;
	}
	
	public function setInterfacePhpTypeDefs(array $interfacePhpTypeDefs) {
		$this->interfacePhpTypeDefs = $interfacePhpTypeDefs;
	}
	
	public function addInterfacePhpTypeDef(PhpTypeDef $interfacePhpTypeDef) {
		$this->interfacePhpTypeDefs[$interfacePhpTypeDef->getTypeName()] = $interfacePhpTypeDef;
	}
	
	public function hasInterfacePhpTypeDef(string $typeName) {
		return isset($this->interfacePhpTypeDefs[$typeName]);
	}
	
	public function generateInterfacesStr() {
		return implode(', ', $this->interfacePhpTypeDefs);
	}
	

// 	public function getInterfaceTypeNames() {
// 		$interfaceTypeNames = array();
	
// 		foreach ($this->interfacePhpTypeDefs as $interfaceName) {
// 			$interfaceTypeNames[] = $this->determineTypeName($interfaceName);
// 		}
	
// 		return $interfaceTypeNames;
// 	}
	
// 	public abstract function determineTypeName($name);
}