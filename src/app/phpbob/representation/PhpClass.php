<?php
namespace phpbob\representation;

use phpbob\Phpbob;
use phpbob\PhprepUtils;
use phpbob\representation\traits\InterfacesTrait;
use n2n\reflection\annotation\AnnotationSet;
use phpbob\representation\anno\PhpAnnotationSet;

class PhpClass extends PhpClassLikeAdapter {
	use InterfacesTrait;
	
	private $final = false;
	private $abstract = false;
	private $static = false;
	
	private $superClassTypeDef;
	private $annotationSet = null;

	public function isFinal() {
		return $this->final;
	}

	public function setFinal(bool $final) {
		$this->final = $final;
	}

	public function isAbstract() {
		return $this->abstract;
	}

	public function setAbstract(bool $abstract) {
		$this->abstract = $abstract;
	}

	public function isStatic() {
		return $this->static;
	}

	public function setStatic(bool $static) {
		$this->static = $static;
	}

	public function getSuperClassTypeDef() {
		return $this->superClassTypeDef;
	}

	public function setSuperClassTypeDef(PhpTypeDef $superClassTypeDef = null) {
		$this->superClassTypeDef = $superClassTypeDef;
	}

	/**
	 * @return PhpAnnotationSet
	 */
	public function getAnnotationSet() {
		if (null === $this->annotationSet) {
			$this->annotationSet = new AnnotationSet();
		}
		return $this->annotationSet;
	}
	
	public function setAnnotationSet(PhpAnnotationSet $annotationSet) {
		$this->annotationSet = $annotationSet;
		
		return $this;
	}
	
	public function implementsInterface($typeName) {
		return $this->hasInterfacePhpTypeDef($typeName);
	}

	public function __toString() {
		$this->annotationSet->applyTypeNames();
		return $this->generateHeader() . $this->generateClassDefinition() . Phpbob::GROUP_STATEMENT_OPEN . 
				PHP_EOL . $this->annotationSet . PHP_EOL .  $this->generateBody() . Phpbob::GROUP_STATEMENT_CLOSE;	
	}

	private function generateHeader() {
		if (!$this->annotationSet->isEmpty()) {
			$this->addUse(PhpAnnotationSet::createAnnoInitUse());
		}
		
		$string = Phpbob::PHP_BLOCK_BEGIN . PHP_EOL;
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
			$extendsClause .= ' ' . Phpbob::KEYWORD_EXTENDS . ' ' . $this->superClassName;
		}
		
		$implementsClause = '';
		if (count($this->interfaceNames) > 0) {
			$implementsClause .= ' ' . Phpbob::KEYWORD_IMPLEMENTS . ' ' . implode(', ', $this->interfaceNames);
		}
		
		return ($this->abstract ? Phpbob::KEYWORD_ABSTRACT . ' ' : '') . Phpbob::KEYWORD_CLASS . ' ' . $this->name . $extendsClause . $implementsClause . ' ';
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
}