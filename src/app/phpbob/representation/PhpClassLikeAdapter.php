<?php
namespace phpbob\representation;

use phpbob\representation\ex\UnknownElementException;
use n2n\util\ex\IllegalStateException;
use phpbob\representation\anno\PhpAnnotationSet;
use phpbob\Phpbob;
use phpbob\representation\traits\PrependingCodeTrait;

abstract class PhpClassLikeAdapter extends PhpTypeAdapter implements PhpClassLike {
	use PrependingCodeTrait;
	
	private $phpAnnotationSet;
	private $phpProperties = [];
	private $phpMethods = [];
	private $phpTraitUses = [];
	
	/**
	 * @return PhpAnnotationSet
	 */
	public function getPhpAnnotationSet() {
		if (null === $this->phpAnnotationSet) {
			$this->phpAnnotationSet = new PhpAnnotationSet($this);
		}
		return $this->phpAnnotationSet;
	}
	
	public function isPhpAnnotationSetAvailable() {
		return null !== $this->phpAnnotationSet && !$this->phpAnnotationSet->isEmpty();
	}
	
	public function setAnnotationSet(PhpAnnotationSet $annotationSet) {
		$this->phpAnnotationSet = $annotationSet;
		
		return $this;
	}
	
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
	 * @param string $propertyName
	 * @param bool $bool
	 * @return boolean
	 */
	public function hasPhpGetter(string $propertyName, bool $bool = false) {
		return $this->hasPhpMethod(self::determineGetterMethodName($propertyName, $bool));
	}
	
	/**
	 * @param string $propertyName
	 * @param bool $bool
	 * @return \phpbob\representation\PhpMethod
	 */
	public function getPhpGetter(string $propertyName, bool $bool = false) {
		return $this->getPhpMethod(self::determineGetterMethodName($propertyName, $bool));
	}
	
	/**
	 * @param string $propertyName
	 * @return boolean
	 */
	public function hasPhpSetter(string $propertyName) {
		return $this->hasPhpMethod(self::determineSetterMethodName($propertyName));
	}
	
	/**
	 * @param string $propertyName
	 * @return \phpbob\representation\PhpMethod
	 */
	public function getPhpSetter(string $propertyName) {
		return $this->getPhpMethod(self::determineSetterMethodName($propertyName));
	}
	
	/**
	 * @param PhpTypeDef
	 */
	public function determinePhpTypeDef(string $propertyName) {
		if ($this->hasPhpSetter($propertyName) && 
				null !== $phpTypeDef = $this->getPhpSetter($propertyName)->getReturnPhpTypeDef()) {
			return $phpTypeDef;
		}
		
		if ($this->hasPhpGetter($propertyName)) {
			$phpGetter = $this->getPhpGetter($propertyName);
			if (null !== ($firstPhpParam = $phpGetter->getFirstPhpParam()) 
					&& (null !== $phpTypeDef = $firstPhpParam->getPhpTypeDef())) {
				return $phpTypeDef;			
			}
		}
		
		if ($this->hasPhpGetter($propertyName)) {
			$phpGetter = $this->getPhpGetter($propertyName);
			if (null !== ($firstPhpParam = $phpGetter->getFirstPhpParam())
					&& (null !== $phpTypeDef = $firstPhpParam->getPhpTypeDef())) {
				return $phpTypeDef;
			}
			
			return new PhpTypeDef('bool');
		}
		
		return null;
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
	 * @param PhpMethod $phpMethod
	 * @return PhpMethod
	 */
	public function createPhpMethodClone(PhpMethod $phpMethod) {
		$phpMethodClone = $this->createPhpMethod($phpMethod->getName())
				->setAbstract($phpMethod->isAbstract())->setClassifier($phpMethod->getClassifier())
				->setFinal($phpMethod->isFinal())->setPrependingCode($phpMethod->getPrependingCode())
				->setMethodCode($phpMethod->getMethodCode())->setReturnPhpTypeDef($phpMethod->getReturnPhpTypeDef());
		
		foreach ($phpMethod->getPhpParams() as $phpParam) {
			$phpMethodClone->createPhpParam($phpParam->getName(), $phpParam->getValue(),
					$phpParam->getPhpTypeDef(), $phpParam->isSplat())->setPassedByReference($phpParam->isPassedByReference());
		}
		
		return $phpMethodClone;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpMethod
	 */
	public function createPhpSetter(string $propertyName, PhpTypeDef $phpTypeDef = null, string $value = null) {
		if (!$this->hasPhpProperty($propertyName)) {
			throw new IllegalStateException('No property with name \'' . $propertyName . '\' available.');
		}
		
		$methodName = self::determineSetterMethodName($propertyName);
		
		$phpMethod = $this->createPhpMethod($methodName);
		
		$phpMethod->createPhpParam($propertyName, $value, $phpTypeDef);
		$phpMethod->setMethodCode('$this->' . $propertyName . ' ' . Phpbob::ASSIGNMENT . ' $' . $propertyName . Phpbob::SINGLE_STATEMENT_STOP);
			
		return $phpMethod;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpMethod
	 */
	public function createPhpGetter(string $propertyName, PhpTypeDef $phpTypeDef = null) {
		if (!$this->hasPhpProperty($propertyName)) {
			throw new IllegalStateException('No property with name \'' . $propertyName . '\' available.');
		}
		
		$methodName = self::determineGetterMethodName($propertyName, (null !== $phpTypeDef && $phpTypeDef->isBool()));
		
		$phpMethod = $this->createPhpMethod($methodName);
		
		$phpMethod->setMethodCode('return $this->' . $propertyName . Phpbob::SINGLE_STATEMENT_STOP);
			
		return $phpMethod;
	}
	
	public function createPhpGetterAndSetter(string $propertyName, PhpTypeDef $phpTypeDef = null, string $value = null) {
		$this->createPhpGetter($propertyName, $phpTypeDef);
		$this->createPhpSetter($propertyName, $phpTypeDef, $value);
		
		return $this;
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
			throw new IllegalStateException('Method with name ' . $name . ' already defined.');
		}
		
		if ($name === PhpAnnotationSet::ANNO_METHOD_NAME) {
			throw new IllegalStateException('Work with ' . get_class($this) . '::getPhpAnnotationSet() to work with Annotations.');
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
	public function createPhpProperty(string $name, string $classifier = Phpbob::CLASSIFIER_PRIVATE): PhpProperty {
		$this->checkPhpPropertyName($name);
		
		$phpProperty = new PhpProperty($this, $classifier, $name);
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
			$typeDefs = array_merge($typeDefs, $phpMethod->getPhpTypeDefs());
		}
		
		foreach ($this->phpTraitUses as $phpTraitUse) {
			$typeDefs[] = $phpTraitUse->getPhpTypeDef();
		}
		
		if ($this->isPhpAnnotationSetAvailable()) {
			$typeDefs = array_merge($typeDefs, $this->phpAnnotationSet->getPhpTypeDefs());
		}
		
		return $typeDefs;
	}
	
	protected function generateBody() {
		$str = '';
		
		if (null !== $this->phpAnnotationSet) {
			$str = $this->phpAnnotationSet . PHP_EOL;
		}
		
		return $str . $this->generateTraitsStr() . $this->generateConstStr() . $this->generatePropertiesStr()  
				. $this->generateMethodStr();
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
		
		return implode(PHP_EOL, $this->phpMethods);
	}
	
	public static function determineSetterMethodName(string $propertyName) {
		return 'set' . ucfirst((string) $propertyName);
	}
	
	public static function determineGetterMethodName(string $propertyName, bool $bool = false) {
		return (($bool) ? 'is' : 'get') . ucfirst((string) $propertyName);
	}
}