<?php
namespace phpbob\representation;

use phpbob\PhprepUtils;

trait TraitsTrait {
	private $traitNames = array();
	
	public function getTraitNames() {
		return $this->traitNames;	
	}
	
	public function setTraitNames(array $traitNames) {
		$this->traitNames = $traitNames;
	}
	
	public function appendTraitNames(array $traitNames) {
		foreach ($traitNames as $traitName) {
			$this->traitNames[(string) $traitName] = (string) $traitName;
		}
	}

	public function hasTrait($typeName) {
		$traitName = PhprepUtils::extractClassName($typeName);
	
		if (!isset($this->traitNames[$traitName])) {
			return isset($this->traitNames[$typeName]);
		}
		
		return $this->determineTypeName($traitName) == $typeName;
	}
	
	public function getTraitTypeNames() {
		$traitTypeNames = array();
		
		foreach ($this->traitNames as $traitName) {
			$traitTypeNames = $this->determineTypeName($traitName);
		}
		
		return $traitTypeNames;
	}
	
	public abstract function determineTypeName($name);
}