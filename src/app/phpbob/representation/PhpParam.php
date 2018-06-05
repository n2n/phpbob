<?php
namespace phpbob\representation;

use phpbob\Phpbob;

class PhpParam extends PhpVariable {
	private $phpParamContainer;
	private $phpTypeDef;
	private $splat = false;
	private $passedByReference = false;
	
	public function __construct(PhpParamContainer $phpParamContainer, string $name, 
			string $value = null, PhpTypeDef $phpTypeDef = null) {
		parent::__construct($name, $value);
		$this->phpParamContainer = $phpParamContainer;
		$this->phpTypeDef = $phpTypeDef;
	}
	
	public function getPhpTypeDef() {
		return $this->phpTypeDef;
	}

	public function setPhpTypeDef(PhpTypeDef $phpTypeDef = null) {
		$this->phpTypeDef = $phpTypeDef;
	}

	public function isSplat() {
		return $this->splat;
	}

	public function setSplat(bool $splat) {
		$this->splat = $splat;
	}

	public function isPassedByReference() {
		return $this->passedByReference;
	}

	public function setPassedByReference(bool $passedByReference) {
		$this->passedByReference = $passedByReference;
	}

	public function getPhpParamContainer() {
		return $this->phpParamContainer;
	}

	// 	public function isPassedByReference() {
// 		return null !== $this->typeName && mb_strlen($this->typeName) > 0 
// 				&& StringUtils::startsWith('&', $this->typeName);
// 	}
	
// 	public function isNullable() {
// 		return null !== $this->typeName || parent::isNullable();
// 	}

	public function __toString() {
		$string = $this->getPrependingString();
		if (null !== $this->typeName) {
			$this->typeName;
			$string .= $this->typeName . ' ';
		}
		
		if ($this->splat) {
			$string .= Phpbob::SPLAT_INDICATOR;
		}
		return $string . $this->getNameValueString(true);
	}
}