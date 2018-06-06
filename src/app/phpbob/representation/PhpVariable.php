<?php
namespace phpbob\representation;

use phpbob\Phpbob;
use phpbob\representation\traits\PrependingCodeTrait;

abstract class PhpVariable {
	use PrependingCodeTrait;
	
	protected $name;
	protected $value;
	
	public function __construct(string $name, string $value = null, 
			string $prependingCode = null) {
		$this->prependingCode = $prependingCode;
		$this->name = $name;
		$this->value = $value;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName(string $name) {
		$this->name = $name;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue(string $value = null) {
		$this->value = $value;
	}
	
// 	public function isNullable() {
// 		return $this->value === Phpbob::KEYWORD_NULL;
// 	}
	
// 	protected function getNameValueString(bool $check = false) {
// 		$string = $this->checkVariableName($this->name, $check);
// 		if (null !== $this->value) {
// 			$string .= ' ' . Phpbob::ASSIGNMENT . ' ' . $this->value; 
// 		}
// 		return $string;
// 	}
	
// 	private function checkVariableName(string $name, bool $check = false) {
// 		if ($check && !StringUtils::startsWith(Phpbob::VARIABLE_PREFIX, $name)) {
// 			return (string) Phpbob::VARIABLE_PREFIX . $name;
// 		}
// 		return (string) $name;
// 	}
}