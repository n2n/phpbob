<?php
namespace phpbob\representation;

use phpbob\representation\ex\UnknownElementException;
use n2n\util\ex\IllegalStateException;
use phpbob\PhprepUtils;

abstract class PhpClassLikeAdapter extends PhpTypeAdapter implements PhpClassLike {
	
	private $phpProperties = [];
	private $phpMethods = [];
	private $phpTraitUses = [];
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpMethod(string $name) {
		return isset($this->phpMethods[$name]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpMethod(string $name) {
		if (!isset($this->phpMethods[$name])) {
			throw new UnknownElementException('No method with name "' . $name . '" given.');
		}
		
		return $this->phpMethods[$name];
	}
	
	/**
	 * @return PhpMethod []
	 */
	public function getPhpMethods() {
		return $this->phpMethods;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpMethod
	 */
	public function createPhpMethod(string $name) {
		$this->checkPhpMethodName($name);
		
		$phpMethod = new PhpMethod($name);
		$that = $this;
		$phpMethod->onNameChange(function($oldName, $newName) use ($that) {
			$that->checkPhpMethodName($newName);
			
			$tmpPhpMethod = $that->phpMethods[$oldName];
			unset($that->phpMethods[$oldName]);
			$that->phpMethods[$newName] = $tmpPhpMethod;
		});
			
		return $phpMethod;
	}
	
	/**
	 * @param string $name
	 * @return \phpbob\representation\PhpClassLikeAdapter
	 */
	public function removePhpMethod(string $name) {
		unset($this->phpMethods[$name]);
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 */
	private function checkPhpMethodName(string $name) {
		if (isset($this->phpMethods[$name])) {
			throw new IllegalStateException('Mmethod with name ' . $name . ' already defined.');
		}
	}
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpProperty(string $name) {
		return isset($this->phpProperties[$name]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpProperty(string $name) {
		if (!isset($this->phpProperties[$name])) {
			throw new UnknownElementException('No property with name "' . $name . '" given.');
		}
		
		return $this->phpProperties[$name];
	}
	
	public function getPhpProperties() {
		return $this->phpProperties;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpProperty
	 */
	public function createPhpProperty(string $name) {
		$this->checkPhpPropertyName($name);
		
		$phpProperty = new PhpProperty($name);
		$that = $this;
		$phpProperty->onNameChange(function($oldName, $newName) use ($that) {
			$that->checkPhpPropertyName($newName);
			
			$tmpPhpProperty = $that->phpProperties[$oldName];
			unset($that->phpProperties[$oldName]);
			$that->phpProperties[$newName] = $tmpPhpProperty;
		});
			
		return $phpProperty;
	}
	
	
	/**
	 * @param string $name
	 * @return \phpbob\representation\PhpClassLikeAdapter
	 */
	public function removePhpProperty(string $name) {
		unset($this->phpProperties[$name]);
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 */
	private function checkPhpPropertyName(string $name) {
		if (isset($this->phpProperties[$name])) {
			throw new IllegalStateException('Interface method with name ' . $name . ' already defined.');
		}
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpTraitUse(string $typeName) {
		return isset($this->phpTraitUses[$typeName]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpTraitUse(string $typeName) {
		if (!isset($this->phpTraitUses[$typeName])) {
			throw new UnknownElementException('No php trait use with typename "' . $typeName . '" given.');
		}
		
		return $this->phpProperties[$typeName];
	}
	
	public function getPhpTraitUses() {
		return $this->phpTraitUses;
	}
	
	/**
	 * @param string $typeName
	 * @param string $localName
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpTraitUse
	 */
	public function createPhpTraitUse(string $typeName, string $localName = null) {
		$this->checkPhpTraitUseTypeName($typeName);
		
		if (null !== $localName) {
			$localName = PhprepUtils::extractClassName($typeName);
		}
		
		$phpTypeDef = new PhpTypeDef($localName, $typeName);
		
		$that = $this;
		$phpTypeDef->onTypeNameChange(function($oldTypeName, $newTypeName) use ($that) {
			$that->checkPhpPropertyName($newTypeName);
			
			$tmpPhpProperty = $that->phpProperties[$oldTypeName];
			unset($that->phpProperties[$oldTypeName]);
			$that->phpProperties[$newTypeName] = $tmpPhpProperty;
		});
		
		$phpTraitUse = new PhpTraitUse($this, $phpTypeDef);
		$this->phpTraitUses[$typeName] = $phpTraitUse;
			
		return $phpTraitUse;
	}
	
	/**
	 * @param string $typeName
	 * @throws IllegalStateException
	 */
	private function checkPhpTraitUseTypeName(string $typeName) {
		if (isset($this->phpTraitUses[$typeName])) {
			throw new IllegalStateException('Trait use ' . $typeName . ' already defined.');
		}
	}
}