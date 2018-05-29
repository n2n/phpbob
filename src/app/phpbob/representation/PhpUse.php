<?php
namespace phpbob\representation;

use phpbob\PhpKeyword;

class PhpUse {
	use PrependingCodeTrait;
	
	const KEYWORD_USE = 'use';
	
	private $typeName;
	
	public function __construct($typeName, $prependingCode = null) {
		$this->typeName = $typeName;
		$this->prependingCode = $prependingCode;
	}
	
	public function getPrependingCode() {
		return $this->prependingCode;
	}

	public function setPrependingCode($prependingCode) {
		$this->prependingCode = $prependingCode;
	}

	public function getTypeName(): string {
		return $this->typeName;
	}

	public function setTypeName($typeName) {
		$this->typeName = $typeName;
	}

	public function __toString() {
		return $this->getPrependingString() . self::KEYWORD_USE . ' ' . $this->typeName
				. PhpKeyword::SINGLE_STATEMENT_STOP;
	}
}