<?php
namespace phpbob\representation;

use n2n\util\StringUtils;
use phpbob\PhprepUtils;
use phpbob\Phpbob;

class PhpTypeDef {
	private $localName;
	private $typeName;
	private $typeNameChangeClosures = [];
	
	public function __construct(string $localName, string $typeName = null) {
		$this->changeName($localName, $typeName);
	}
	
	public function changeName(string $localName, string $typeName = null) {
		$this->valNameAssociationCorrect($localName, $typeName);
		
		$this->localName = $localName;
		if ($this->typeName !== $typeName) {
			$this->triggerTypeNameChange($this->typeName, $typeName);
		}
		
		$this->typeName = $typeName;
	}
	
	public function getLocalName() {
		return $this->localName;
	}

	public function getTypeName() {
		return $this->typeName;
	}
	
	public function onTypeNameChange(\Closure $typeNameChangeClosure) {
		$this->typeNameChangeClosures[] = $typeNameChangeClosure;
	}
	
	private function triggerTypeNameChange(string $oldTypeName, string $newTypeName) {
		foreach ($this->typeNameChangeClosures as $typeNameChangeClosure) {
			$typeNameChangeClosure($oldTypeName, $newTypeName);
		}
	}

	public function valNameAssociationCorrect(string $localName, string $typeName = null) {
		if (null === $typeName) return;
		if (StringUtils::endsWith($localName, $typeName)) return;
		
		$localNameParts = PhprepUtils::extractTypeNames($localName);
		
		$asPart = array_shift($localNameParts);
		
		if (StringUtils::endsWith(implode(Phpbob::NAMESPACE_SEPERATOR, $localNameParts))) return;
		
		throw new \InvalidArgumentException('Invalid local name ' . $localName . ' for typename ' . $typeName);
	}
}