<?php
namespace phpbob\representation\traits;

use phpbob\representation\PhpNamespaceElementCreator;

trait PhpNamespaceElementTrait {
	private $phpFile;
	private $phpNamespace;
	
	public function getPhpFile() {
		return $this->phpFile;
	}
	
	public function getPhpNamespace() {
		return $this->phpNamespace;
	}
	
	/**
	 * @return PhpNamespaceElementCreator
	 */
	protected function determinePhpNamespaceElementCreator() {
		if (null !== $this->phpNamespace) return $this->phpNamespace;
		
		return $this->phpFile;
	}
}