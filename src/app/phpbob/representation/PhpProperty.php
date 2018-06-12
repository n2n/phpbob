<?php
namespace phpbob\representation;

use phpbob\Phpbob;
use n2n\reflection\ArgUtils;

class PhpProperty extends PhpVariable {

	private $phpClassLike;
	private $classifier;
	private $static;
	
	public function __construct(PhpClassLike $phpClassLike, string $classifier, 
			string $name, string $value = null, string $prependingCode = null) {
		parent::__construct($name, $value, $prependingCode);
		$this->phpClassLike = $phpClassLike;
		$this->classifier = $classifier;
	}
	
	public function setStatic(bool $static) {
		$this->static = $static;
		
		return $this;
	}
	
	public function isStatic() {
		return $this->static;
	}
	
	public function getClassifier() {
		return $this->classifier;
	}

	public function setClassifier(string $classifier) {
		ArgUtils::valEnum($classifier, Phpbob::getClassifiers());
		
		$this->classifier = $classifier;
	}

	public function __toString() {
		$string = $this->getPrependingString() . "\t";
		if (null !== $this->classifier) {
			$string .= $this->classifier . ' ';
		}
		
		return $string .  $this->getNameValueString() . Phpbob::SINGLE_STATEMENT_STOP . PHP_EOL;
	}
}