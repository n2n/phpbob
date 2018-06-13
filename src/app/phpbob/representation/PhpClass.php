<?php
namespace phpbob\representation;

use phpbob\Phpbob;
use phpbob\representation\traits\InterfacesTrait;

class PhpClass extends PhpClassLikeAdapter {
	use InterfacesTrait;
	
	private $final = false;
	private $abstract = false;
	private $static = false;
	
	private $superClassTypeDef;

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
	
	public function implementsInterface($typeName) {
		return $this->hasInterfacePhpTypeDef($typeName);
	}

	public function __toString() {
		$str = $this->getPrependingString() . $this->generateClassDefinition() . Phpbob::GROUP_STATEMENT_OPEN . PHP_EOL;

		return $str .  $this->generateBody() . Phpbob::GROUP_STATEMENT_CLOSE;	
	}

	private function generateClassDefinition() {
		$extendsClause = '';
		if (null !== $this->superClassTypeDef) {
			$extendsClause .= ' ' . Phpbob::KEYWORD_EXTENDS . ' ' . $this->superClassTypeDef;
		}
		
		$implementsClause = '';
		if (count($this->interfacePhpTypeDefs) > 0) {
			$implementsClause .= ' ' . Phpbob::KEYWORD_IMPLEMENTS . ' ' . $this->generateInterfacesStr();
		}
		
		return ($this->abstract ? Phpbob::KEYWORD_ABSTRACT . ' ' : '') . Phpbob::KEYWORD_CLASS . ' ' . $this->getName() . $extendsClause . $implementsClause . ' ';
	}
	
	public function getPhpTypeDefs() : array {
		$phpTypeDefs = array_merge(parent::getPhpTypeDefs(), $this->interfacePhpTypeDefs);
		
		if (null !== $this->superClassTypeDef) {
			$phpTypeDefs[] = $this->superClassTypeDef;
		}
		
		return $phpTypeDefs;
	}
}