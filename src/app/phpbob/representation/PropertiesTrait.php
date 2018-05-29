<?php
namespace phpbob\representation;

use n2n\reflection\ArgUtils;

trait PropertiesTrait {
	/**
	 * @var PhpProperty []
	 */
	protected $properties = array();

	public function getProperties() {
		return $this->properties;
	}
	
	public function setProperties(array $properties) {
		ArgUtils::valArray($properties, PhpProperty::class);
		$this->properties = array();
		foreach ($properties as $property) {
			$this->properties[$property->getName()] = $property;
		}

		return $this;
	}
	
	public function addProperty(PhpProperty $property) {
		$this->properties[$property->getName()] = $property;
		
		return $this;
	}
	
	public function hasProperty(string $propertyName = null) {
		if (null === $propertyName) return false;
		
		return isset($this->properties[(string) $propertyName]);
	}
	
	public function getProperty(string $propertyName) {
		if (!$this->hasProperty($propertyName)) return null;
		
		return $this->properties[$propertyName];
	}
	
	public function removeProperty(string $propertyName) {
		unset($this->properties[$propertyName]);
		
		return $this;
	}
	
	public function updateProperty(string $propertyName, string $newPropertyName) {
		$this->properties[$newPropertyName] = $this->properties[$propertyName];
		unset($this->properties[$propertyName]);
		$this->properties[$newPropertyName]->setName($newPropertyName);
	}
}