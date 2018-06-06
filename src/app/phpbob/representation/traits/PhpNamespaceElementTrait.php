<?php
namespace phpbob\representation\traits;

trait PhpNamespaceElementTrait {
	private $phpFile;
	private $phpNamespace;
	
	public function getPhpFile() {
		return $this->phpFile;
	}
	
	public function getPhpNamespace() {
		return $this->phpNamespace;
	}
	
	private function determinePhpNamespaceElementCreator() {
		if (null !== $this->phpNamespace) return $this->phpNamespace;
		
		$this->phpFile;
	}
}