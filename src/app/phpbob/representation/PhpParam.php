<?php
namespace phpbob\representation;

use n2n\util\StringUtils;
use phpbob\PhpKeyword;

class PhpParam extends PhpVariable {
	private $typeName;
	private $splat;
	
	public function __construct(string $name, string $value = null, string $typeName = null, bool $splat = false) {
		parent::__construct($name, $value);
		$this->typeName = $typeName;
		$this->splat = $splat;
	}
	
	public function getTypeName() {
		return $this->typeName;
	}

	public function setTypeName(string $typeName = null) {
		$this->typeName = $typeName;
	}
	
	public function isPassedByReference() {
		return null !== $this->typeName && mb_strlen($this->typeName) > 0 
				&& StringUtils::startsWith('&', $this->typeName);
	}
	
	public function isNullable() {
		return null !== $this->typeName || parent::isNullable();
	}

	public function __toString() {
		$string = $this->getPrependingString();
		if (null !== $this->typeName) {
			$this->typeName;
			$string .= $this->typeName . ' ';
		}
		
		if ($this->splat) {
			$string .= PhpKeyword::SPLAT_INDICATOR;
		}
		return $string . $this->getNameValueString(true);
	}
}