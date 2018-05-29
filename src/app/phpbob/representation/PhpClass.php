<?php
namespace phpbob\representation;

use phpbob\PhpKeyword;
use phpbob\PhprepUtils;

class PhpClass extends PhpTypeAdapter implements PhpTraitContainer {
	
	use InterfacesTrait;
	use PropertiesTrait;
	use TraitsTrait;
	
	private $final = false;
	private $abstract = false;
	private $static = false;
	
	private $superClassName;
	private $annotationSet;
	
	public function __construct($typeName) {
		parent::__construct($typeName);
		
		$this->annotationSet = new PhpAnnotationSet($this);
	}
	
	public function isFinal() {
		return $this->final;
	}
	
	public function setFinal($final) {
		$this->final = (bool) $final;
	}
	
	public function isAbstract() {
		return $this->abstract;
	}
	
	public function setAbstract($abstract) {
		$this->abstract = (bool) $abstract;
	}

	public function getClassName() {
		return $this->name;
	}

	public function setClassName($className) {
		$this->name = $className;
	}

	public function getSuperClassName() {
		return $this->superClassName;
	}

	public function setSuperClassName($superClassName) {
		$namespace = PhprepUtils::extractNamespace($superClassName);
		
		if (null !== $namespace && '.' !== $namespace && $namespace !== $this->namespace->getNamespace()) {
			$this->addUse(new PhpUse($superClassName));
		}
		
		$this->superClassName = PhprepUtils::extractClassName($superClassName);
	}
	/**
	 * @return PhpAnnotationSet
	 */
	public function getAnnotationSet() {
		return $this->annotationSet;
	}
	
	public function setAnnotationSet(PhpAnnotationSet $annotationSet) {
		$this->annotationSet = $annotationSet;
		return $this;
	}
	
	public function implementsInterface($typeName) {
		return $this->hasInterface($typeName);
	}

	public function __toString() {
		$this->annotationSet->applyTypeNames();
		return $this->generateHeader() . $this->generateClassDefinition() . PhpKeyword::GROUP_STATEMENT_OPEN . 
				PHP_EOL . $this->annotationSet . PHP_EOL .  $this->generateBody() . PhpKeyword::GROUP_STATEMENT_CLOSE;	
	}

	private function generateHeader() {
		if (!$this->annotationSet->isEmpty()) {
			$this->addUse(PhpAnnotationSet::createAnnoInitUse());
		}
		
		$string = PhpKeyword::PHP_BLOCK_BEGIN . PHP_EOL;
		if (null !== $this->namespace) {
			$string .= PhprepUtils::removeTrailingWhiteSpaces($this->namespace) . PHP_EOL . PHP_EOL;
		}
		
		if (count($this->uses) > 0) {
			$string .= PhprepUtils::removeTrailingWhiteSpaces(implode(PHP_EOL, $this->uses)) 
					. PHP_EOL . PHP_EOL;
		}

		return $string . $this->getPrependingString();
	}

	private function generateClassDefinition() {
		$extendsClause = '';
		if (strlen($this->superClassName) > 0) {
			$extendsClause .= ' ' . PhpKeyword::KEYWORD_EXTENDS . ' ' . $this->superClassName;
		}
		
		$implementsClause = '';
		if (count($this->interfaceNames) > 0) {
			$implementsClause .= ' ' . PhpKeyword::KEYWORD_IMPLEMENTS . ' ' . implode(', ', $this->interfaceNames);
		}
		
		return ($this->abstract ? PhpKeyword::KEYWORD_ABSTRACT . ' ' : '') . PhpKeyword::KEYWORD_CLASS . ' ' . $this->name . $extendsClause . $implementsClause . ' ';
	}
	
	private function generateBody() {
		$string = '';
		if (count($this->constants) > 0) {
			$string .= PhprepUtils::removeTrailingWhiteSpaces(implode(PHP_EOL, $this->constants)) . PHP_EOL . PHP_EOL;
		}
		
		if (count($this->properties) > 0) {
			$string .= "\t" . trim(implode(PHP_EOL, $this->properties)) . PHP_EOL . PHP_EOL;
		}
		
		if (count($this->methods) > 0) {
			foreach ($this->methods as $method) {
				$string .=  "\t" . trim((string) $method) . PHP_EOL . PHP_EOL ;
			}
		}
		
		return rtrim($string) . PHP_EOL;
	}
	
	public function isClass() {
		return true;
	}
}