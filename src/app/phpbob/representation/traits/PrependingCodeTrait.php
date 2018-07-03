<?php
namespace phpbob\representation\traits;

use phpbob\PhpbobUtils;
use n2n\util\StringUtils;

trait PrependingCodeTrait {
	
	protected $prependingCode = null;
	
	public function getPrependingCode() {
		return $this->prependingCode;
	}
	
	public function setPrependingCode(string $prependingCode = null) {
		$this->prependingCode = $prependingCode;
		
		return $this;
	}
	
	public function hasPrependingCode() {
		return !empty(trim($this->prependingCode));
	}
	
	protected function getPrependingString() {
		if (!$this->hasPrependingCode()) {
			return '';
		}

		$this->prependingCode = PhpbobUtils::removeLeadingWhiteSpaces($this->prependingCode);
		
		if (StringUtils::endsWith($this->prependingCode, PHP_EOL)) {
			return $this->prependingCode;
		}
		
		return $this->prependingCode . PHP_EOL;
	}
}