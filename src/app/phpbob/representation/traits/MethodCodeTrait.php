<?php
namespace phpbob\representation\traits;

use phpbob\PhprepUtils;

trait MethodCodeTrait {
	private $methodCode;
	
	
	public function getMethodCode() {
		return $this->methodCode;
	}
	
	public function setMethodCode(string $methodCode = null) {
		$this->methodCode = (string) $methodCode;
		
		return $this;
	}
	
	public function generateMethodCodeStr(int $numLeadingTabs) {
		if (empty($this->methodCode)) return '';
		
		$str = PHP_EOL;
		foreach (explode(PHP_EOL, $this->methodCode) as $methodCodeLine) {
			if (preg_match('/^\s*$/', $methodCodeLine)) continue;
			
			$str .= str_repeat("\t", $numLeadingTabs) . PhprepUtils::removeLeadingWhiteSpaces($methodCodeLine) . PHP_EOL;
		}
		
		return $str;
	}
}