<?php
namespace phpbob\representation;

use phpbob\PhprepUtils;

trait InterfacesTrait {
	protected $interfaceNames = array();
	
	public function getInterfaceNames() {
		return $this->interfaceNames;
	}
	
	public function setInterfaceNames(array $interfaceNames) {
		$this->interfaceNames = $interfaceNames;
	}
	
	public function addInterfaceName($interfaceName) {
		$this->interfaceNames[(string) $interfaceName] = (string) $interfaceName;
	}
	
	protected function hasInterface($typeName) {
		$interfaceName = PhprepUtils::extractClassName($typeName);
	
		if (!isset($this->interfaceNames[$interfaceName])) {
			return isset($this->interfaceNames[$typeName]);
		}
	
		return $this->determineTypeName($interfaceName) == $typeName;
	}
	

	public function getInterfaceTypeNames() {
		$interfaceTypeNames = array();
	
		foreach ($this->interfaceNames as $interfaceName) {
			$interfaceTypeNames[] = $this->determineTypeName($interfaceName);
		}
	
		return $interfaceTypeNames;
	}
	
	public abstract function determineTypeName($name);
}