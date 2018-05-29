<?php
namespace phpbob\representation;

use phpbob\PhpKeyword;

class PhpGetter extends PhpMethod {
	
	public function __construct($propertyName, $boolean = false, $prependingCode = null) {
		parent::__construct((($boolean) ? 'is' : 'get') . ucfirst((string) $propertyName), null,
				PhpKeyword::CLASSIFIER_PUBLIC, $prependingCode);
		$this->setMethodCode("\t\t" . PhpKeyword::KEYWORD_RETURN . ' $this->' . $propertyName 
				. PhpKeyword::SINGLE_STATEMENT_STOP);
	}
}
