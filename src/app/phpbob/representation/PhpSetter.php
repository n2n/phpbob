<?php
namespace phpbob\representation;

use phpbob\PhpKeyword;

class PhpSetter extends PhpMethod {
	
	public function __construct(string $propertyName, bool $boolean = false, string $typeName = null, 
			bool $required = false, string $prependingCode = null) {
		parent::__construct('set' . ucfirst((string) $propertyName), 
				array(new PhpParam('$' . $propertyName, (null !== $typeName && !$required) ? 'null' : null , $typeName)), 
				PhpKeyword::CLASSIFIER_PUBLIC,  $prependingCode);
		$this->setMethodCode("\t\t" . '$this->' . $propertyName . ' ' .  
				PhpKeyword::ASSIGNMENT . ' ' . ($boolean ? '(bool) ' : '')
				. PhpKeyword::VARIABLE_PREFIX . $propertyName . PhpKeyword::SINGLE_STATEMENT_STOP. PHP_EOL);
	}
}
