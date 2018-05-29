<?php
namespace phpbob\representation;

use phpbob\PhpKeyword;

class PhpProperty extends PhpVariable {

	private $classifier;
	private $static;
	
	public function __construct(string $classifier, string $name, string $value = null, string $prependingCode = null) {
		parent::__construct($name, $value, $prependingCode);
		$this->classifier = $classifier;
	}
	
	public function setStatic(bool $static) {
		$this->static = $static;
	}
	
	public function isStatic() {
		return $this->static;
	}
	
	public function getClassifier() {
		return $this->classifier;
	}

	public function setClassifier(string $classifier) {
		$this->classifier = $classifier;
	}

	public function __toString() {
		$string = $this->getPrependingString() . "\t";
		if (null !== $this->classifier) {
			$string .= $this->classifier . ' ';
		}
		return $string .  $this->getNameValueString(true) . PhpKeyword::SINGLE_STATEMENT_STOP;
	}
}