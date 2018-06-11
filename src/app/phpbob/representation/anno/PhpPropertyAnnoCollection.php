<?php
namespace phpbob\representation\anno;


class PhpPropertyAnnoCollection extends PhpAnnoCollectionAdapter {
	private $propertyName;
	private $propertyNameChangeClosures = [];
	
	public function __construct(PhpAnnotationSet $phpAnnotationSet, string $propertyName, $prependingCode = null) {
		parent::__construct($phpAnnotationSet, $prependingCode);
		$this->propertyName = $propertyName;
	}
	
	public function getPropertyName() {
		return $this->propertyName;
	}
	
	public function setPropertyName(string $propertyName) {
		if ($this->propertyName !== $propertyName) {
			$this->triggerPropertyNameChange($this->propertyName, $propertyName);
			$this->propertyName = $propertyName;
		}
		
		return $this;
	}
	
	public function onPropertyNameChange(\Closure $closure) {
		$this->propertyNameChangeClosures[] = $closure;
	}
	
	private function triggerPropertyNameChange(string $oldPropertyName, string $newPropertyName) {
		foreach ($this->propertyNameChangeClosures as $propertyNameChangeClosure) {
			$propertyNameChangeClosure($oldPropertyName, $newPropertyName);
		}
	}
}