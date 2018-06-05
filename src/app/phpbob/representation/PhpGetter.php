<?php
namespace phpbob\representation;

use phpbob\Phpbob;

class PhpGetter extends PhpMethod {
	
	public function __construct($propertyName, $boolean = false, $prependingCode = null) {
		parent::__construct((($boolean) ? 'is' : 'get') . ucfirst((string) $propertyName), null,
				Phpbob::CLASSIFIER_PUBLIC, $prependingCode);
		$this->setMethodCode("\t\t" . Phpbob::KEYWORD_RETURN . ' $this->' . $propertyName 
				. Phpbob::SINGLE_STATEMENT_STOP);
	}
}
