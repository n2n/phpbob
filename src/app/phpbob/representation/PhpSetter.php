<?php
namespace phpbob\representation;

use phpbob\Phpbob;

class PhpSetter extends PhpMethod {
	
	public function __construct(string $propertyName, bool $boolean = false, string $typeName = null, 
			bool $required = false, string $prependingCode = null) {
		parent::__construct('set' . ucfirst((string) $propertyName), 
				array(new PhpParam('$' . $propertyName, (null !== $typeName && !$required) ? 'null' : null , $typeName)), 
				Phpbob::CLASSIFIER_PUBLIC,  $prependingCode);
		$this->setMethodCode("\t\t" . '$this->' . $propertyName . ' ' .  
				Phpbob::ASSIGNMENT . ' ' . ($boolean ? '(bool) ' : '')
				. Phpbob::VARIABLE_PREFIX . $propertyName . Phpbob::SINGLE_STATEMENT_STOP. PHP_EOL);
	}
}
