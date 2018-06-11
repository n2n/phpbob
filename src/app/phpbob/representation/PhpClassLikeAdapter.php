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
	public function hasPhpMethod(string $name): bool {
		return isset($this->phpMethods[$name]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpMethod(string $name): PhpMethod {
		if (!isset($this->phpMethods[$name])) {
			throw new UnknownElementException('No method with name "' . $name . '" given.');
		}
		
		return $this->phpMethods[$name];
	}
	
	/**
	 * @return PhpMethod []
	 */
	public function getPhpMethods(): array {
		return $this->phpMethods;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpMethod
	 */
	public function createPhpMethod(string $name): PhpMethod {
		$this->checkPhpMethodName($name);
		
		$phpMethod = new PhpMethod($this, $name);
		$that = $this;
		$phpMethod->onNameChange(function($oldName, $newName) use ($that) {
			$that->checkPhpMethodName($newName);
			
			$tmpPhpMethod = $that->phpMethods[$oldName];
			unset($that->phpMethods[$oldName]);
			$that->phpMethods[$newName] = $tmpPhpMethod;
		});
		
		$this->phpMethods[$name] = $phpMethod;
			
		return $phpMethod;
	}
	
	/**
	 * @param string $name
	 * @return \phpbob\representation\PhpClassLikeAdapter
	 */
	public function removePhpMethod(string $name): PhpClassLike {
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
	public function hasPhpProperty(string $name): bool {
		return isset($this->phpProperties[$name]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpProperty(string $name): PhpProperty {
		if (!isset($this->phpProperties[$name])) {
			throw new UnknownElementException('No property with name "' . $name . '" given.');
		}
		
		return $this->phpProperties[$name];
	}
	
	/**
	 * @return PhpProperty []
	 */
	public function getPhpProperties(): array {
		return $this->phpProperties;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpProperty
	 */
	public function createPhpProperty(string $name): PhpProperty {
		$this->checkPhpPropertyName($name);
		
		$phpProperty = new PhpProperty($name);
		$that = $this;
		$phpProperty->onNameChange(function($oldName, $newName) use ($that) {
			$that->checkPhpPropertyName($newName);
			
			$tmpPhpProperty = $that->phpProperties[$oldName];
			unset($that->phpProperties[$oldName]);
			$that->phpProperties[$newName] = $tmpPhpProperty;
		});
		
		$this->phpProperties[$name] = $phpProperty;
			
		return $phpProperty;
	}
	
	
	/**
	 * @param string $name
	 * @return \phpbob\representation\PhpClassLikeAdapter
	 */
	public function removePhpProperty(string $name): PhpClassLike {
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
	public function hasPhpTraitUse(string $typeName): bool {
		return isset($this->phpTraitUses[$typeName]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpTraitUse(string $typeName): PhpTraitUse {
		if (!isset($this->phpTraitUses[$typeName])) {
			throw new UnknownElementException('No php trait use with typename "' . $typeName . '" given.');
		}
		
		return $this->phpProperties[$typeName];
	}
	
	public function getPhpTraitUses(): array {
		return $this->phpTraitUses;
	}
	
	/**
	 * @param string $typeName
	 * @param string $localName
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpTraitUse
	 */
	public function createPhpTraitUse(string $typeName, string $localName = null): PhpTraitUse {
		$this->checkPhpTraitUseTypeName($typeName);
		
		if (null === $localName) {
			$localName = $typeName;
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
	
	public function getPhpTypeDefs() : array {
		$typeDefs = [];
		
		foreach ($this->phpMethods as $phpMethod) {
			$typeDefs += $phpMethod->getPhpTypeDefs();
		}
		
		foreach ($this->phpTraitUses as $phpTraitUse) {
			$typeDefs[] = $phpTraitUse->getPhpTypeDef();
		}
		
		return $typeDefs;
	}
	
	protected function generateBody() {
		return $this->generateTraitsStr() . $this->generateConstStr() . $this->generatePropertiesStr()  
				. $this->generateMethodStr() . PHP_EOL;
	}
	
	protected function generateTraitsStr() {
		if (empty($this->phpTraitUses)) return '';
		
		return implode('', $this->phpTraitUses) . PHP_EOL; 
	}
	
	protected function generatePropertiesStr() {
		if (empty($this->phpProperties)) return '';
		
		return implode('', $this->phpProperties) . PHP_EOL;
	}
	
	protected function generateMethodStr() {
		if (empty($this->phpMethods)) return '';
		
		return implode('', $this->phpMethods)  . PHP_EOL;
	}
}