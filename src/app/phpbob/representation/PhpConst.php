<?php
namespace phpbob\representation;

use phpbob\PhpKeyword;
class PhpConst extends PhpVariable {
	
	public function __construct($name, $value, $prependingCode = null) {
		parent::__construct($name, $value, $prependingCode);
	}
	
	public function __toString() {
		return $this->getPrependingString() . 
				"\t" . PhpKeyword::KEYWORD_CONST . ' ' . $this->getNameValueString() . PhpKeyword::SINGLE_STATEMENT_STOP;
	}
}