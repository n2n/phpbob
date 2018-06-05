<?php
namespace phpbob\representation;

use phpbob\Phpbob;
use phpbob\representation\traits\PrependingCodeTrait;

class PhpNamespace {
	use PrependingCodeTrait;
	
	private $name;
	private $bracketedSyntax;
	
	public function __construct(PhpFile $phpFile, 
			string $name = null, string $prependingCode = null, bool $bracketedSyntax = false) {
		$this->name = $name;
		$this->prependingCode = $prependingCode;
		$this->bracketedSyntax = $bracketedSyntax;
	}
	
	public function getNamespace() {
		return $this->name;
	}

	public function setNamespace($namespace) {
		$this->name = $namespace;
	}

	public function __toString() {
		if (null === $this->name) return '';
		
		return $this->getPrependingString() . Phpbob::KEYWORD_NAMESPACE . ' ' . $this->name 
				. Phpbob::SINGLE_STATEMENT_STOP;
	}
}