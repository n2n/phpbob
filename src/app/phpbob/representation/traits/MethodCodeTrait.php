<?php
namespace phpbob\representation\traits;

use n2n\util\StringUtils;

trait MethodCodeTrait {
	private $methodCode;
	
	
	public function getMethodCode() {
		return $this->methodCode;
	}
	
	public function setMethodCode(string $methodCode = null) {
		$this->methodCode = (string) $methodCode;
		
		return $this;
	}
	
	public function generateMethodCodeStr() {
		if (empty($this->methodCode)) return '';
		$methodCodeStr = $this->methodCode;
		
		if (!StringUtils::startsWith(PHP_EOL, $this->methodCode)) {
			$methodCodeStr = PHP_EOL . $methodCodeStr;
		}
		
		if (!StringUtils::endsWith(PHP_EOL, $this->methodCode)) {
			$methodCodeStr = $methodCodeStr . PHP_EOL;
		}
		return $methodCodeStr;
	}
}