<?php
namespace phpbob\representation;

use phpbob\representation\traits\NameChangeSubjectTrait;

class PhpFunction extends PhpParamContainerAdapter implements PhpNamespaceElement {
	use NameChangeSubjectTrait;
	
	private $phpFile;
	private $phpNamespace;
	
	public function __construct(PhpFile $phpFile, string $name, PhpNamespace $phpNamespace = null) {
		$this->phpFile = $phpFile;
		$this->name = $name;
		$this->phpNamespace = $phpNamespace;
	}
	
	public function getPhpFile() {
		return $this->phpFile;
	}

	public function getPhpNamespace() {
		return $this->phpNamespace;
	}
}