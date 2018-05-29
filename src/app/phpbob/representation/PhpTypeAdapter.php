<?php
namespace phpbob\representation;

use phpbob\PhprepUtils;
class PhpTypeAdapter implements PhpType {
	use PrependingCodeTrait;
	
	protected $namespace;
	protected $uses = array();
	protected $name;
	
	protected $constants = array();
	protected $methods = array();
	
	public function __construct(string $typeName) {
		$this->setTypeName($typeName);
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	/**
	 * @param string $className
	 * @return PhpUse
	 */
	public function getUseForName($name) {
		if (!isset($this->uses[$name])) return null;
	
		return $this->uses[$name];
	}
	
	public function determineTypeName($name) {
		if (null !== ($phpUse = $this->getUseForName($name))) return $phpUse->getTypeName();
		if ($name !== PhprepUtils::extractClassName($name)) return $name;
		
		return $this->namespace->getNamespace() . PhprepUtils::NAMESPACE_SEPERATOR . $name;
	}
	
	public function setTypeName(string $typeName) {
		$this->name = PhprepUtils::extractClassName($typeName);
		$this->namespace = new PhpNamespace(PhprepUtils::extractNamespace($typeName));
	}

	public function getNamespace() {
		return $this->namespace;
	}
	
	public function setNamespace(PhpNamespace $namespace) {
		$this->namespace = $namespace;
	}
	
	public function getUses() {
		return $this->uses;
	}
	
	public function setUses(array $uses) {
		$this->uses = $uses;
		return $this;
	}
	
	public function addUse(PhpUse $use) {
		$this->uses[PhprepUtils::extractClassName($use->getTypeName())] = $use;
		return $this;
	}
	
	public function hasUse(PhpUse $use) {
		return isset($this->uses[PhprepUtils::extractClassName($use->getTypeName())]);
	}
	
	public function removeUse(PhpUse $use) {
		if ($this->hasUse($use)) {
			unset($this->uses[PhprepUtils::extractClassName($use->getTypeName())]);
		}
		
		return $this;
	}
	
	public function getUseForTypeName(string $typeName) {
		$className = PhprepUtils::extractClassName($typeName);
		if (!isset($this->uses[$className]) || $this->uses[$className]->getTypeName() !== $typeName) return null;
		
		return $this->uses[$className];
	}
	
	public function removeUseWithTypeName(string $typeName) {
		$use = $this->getUseForTypeName($typeName);
		if (null === $use) return;
		
		return $this->removeUse($use);
	}

	/**
	 * @return PhpMethod []
	 */
	public function getMethods() {
		return $this->methods;
	}
	
	public function setMedhods(array $methods) {
		$this->methods = array();
		
		foreach ($methods as $method) {
			$this->addMethod($method);
		}
		
		return $this;
	}
	
	public function addMethod(PhpMethod $method) {
		$this->methods[$method->getName()] = $method;
		$method->determineParamTypeNames($this);
		
		return $this;
	}
	
	public function hasMethod($methodName) {
		return isset($this->methods[$methodName]);
	}
	/**
	 * @param string $methodName
	 * @return PhpMethod
	 */
	public function getMethod($methodName) {
		if (!$this->hasMethod($methodName)) return null;
		
		return $this->methods[$methodName];
	}
	
	public function updateMethod($methodName, $newMethodName) {
		if (!$this->hasMethod($methodName)) return;
		$this->methods[$newMethodName] = $this->methods[$methodName];
		unset($this->methods[$methodName]);
		
		$this->methods[$newMethodName]->setName($newMethodName);
	}
	
	public function removeMethod($methodName) {
		if ($this->hasMethod($methodName)) {
			unset($this->methods[$methodName]);
		}
		
		return $this;
	}

	public function getConstants() {
		return $this->constants;
	}
	
	public function setConstants(array $constants) {
		$this->constants = $constants;
		
		return $this;
	}
	
	public function getConstant($name) {
		if (!isset($this->constants[$name])) return null;
		
		return $this->constants[$name];
	}
	
	public function addConstant(PhpConst $const) {
		$this->constants[$const->getName()] = $const;
		
		return $this;
	}
	
	public function getTypeName(): string {
		return $this->namespace->getNamespace() . PhprepUtils::NAMESPACE_SEPERATOR . $this->name;
	}
	
	public function isClass() {
		return false;
	}
	
	public function isInterface() {
		return false;
	}
	
	public function isTrait() {
		return false;
	}
	
	public function extractUse(string $typeName): string {
		if (PhprepUtils::isInRootNamespace($typeName) || null === PhprepUtils::extractNamespace($typeName)) return $typeName;
		
		$this->addUse(new PhpUse($typeName));
		return PhprepUtils::extractClassName($typeName);
	}
 }