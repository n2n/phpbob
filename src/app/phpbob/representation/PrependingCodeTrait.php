<?php
namespace phpbob\representation;

use phpbob\PhprepUtils;
use n2n\util\StringUtils;

trait PrependingCodeTrait {
	
	protected $prependingCode = null;
	
	public function getPrependingCode() {
		return $this->prependingCode;
	}
	
	public function setPrependingCode($prependingCode) {
		$this->prependingCode = $prependingCode;
	}
	
	public function hasPrependingCode() {
		return !empty(trim($this->prependingCode));
	}
	
	protected function getPrependingString() {
		if (!$this->hasPrependingCode()) {
			return '';
		}

		$this->prependingCode = PhprepUtils::removeLeadingWhiteSpaces($this->prependingCode);
		
		if (StringUtils::endsWith($this->prependingCode, PHP_EOL)) {
			return $this->prependingCode;
		}
		
		return $this->prependingCode . PHP_EOL;
	}
}