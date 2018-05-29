<?php
namespace phpbob\representation;

use phpbob\PhpKeyword;
use n2n\util\StringUtils;

abstract class PhpVariable {
	use PrependingCodeTrait;
	
	protected $name;
	protected $value;
	
	public function __construct(string $name, string $value = null, string $prependingCode = null) {
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

	public function setValue(string $value) {
		$this->value = $value;
	}
	
	public function isNullable() {
		return $this->value === PhpKeyword::KEYWORD_NULL;
	}
	
	protected function getNameValueString(bool $check = false) {
		$string = $this->checkVariableName($this->name, $check);
		if (null !== $this->value) {
			$string .= ' ' . PhpKeyword::ASSIGNMENT . ' ' . $this->value; 
		}
		return $string;
	}
	
	private function checkVariableName(string $name, bool$check = false) {
		if ($check && !StringUtils::startsWith(PhpKeyword::VARIABLE_PREFIX, $name)) {
			return (string) PhpKeyword::VARIABLE_PREFIX . $name;
		}
		return (string) $name;
	}
}