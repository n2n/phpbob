<?php
namespace phpbob\representation;

use phpbob\Phpbob;

class PhpFile extends PhpNamespaceElementCreator {
	
	public function __construct() {
		parent::__construct(new PhpElementFactory($this));
	}
	
	/**
	 * @return PhpFileElement []
	 */
	public function getPhpFileElements() {
		return $this->phpElementFactory->getPhpFileElements();
	}
	
	public function hasNamespaces() {
		return $this->phpElementFactory->hasNamespaces();
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpNamespace(string $name) {
		return $this->phpElementFactory->hasPhpNamespace($name);
	}
	
	/**
	 * @param string $name
	 * @return PhpNamespace
	 */
	public function getPhpNamespace(string $name) {
		return $this->phpElementFactory->getPhpNamespace($name);
	}

	public function getPhpNameSpaces() {
		return $this->phpElementFactory->getPhpNameSpaces();
	}
	
	/**
	 * @param string $name
	 * @param PhpTypeDef $returnPhpTypeDef
	 * @throws IllegalStateException
	 * @return PhpNamespace
	 */
	public function createPhpNamespace(string $name) {
		return $this->phpElementFactory->createPhpNamespace($name);
	}
	
	/**
	 * @param string $name
	 */
	public function removePhpNamespace(string $name) {
		$this->phpElementFactory->removePhpNamespace($name);
	
		return $this;
	}
	
	public function getStringRepresentation() {
		$this->phpElementFactory->resolvePhpTypeDefs();

		return Phpbob::PHP_BLOCK_BEGIN . PHP_EOL . $this->phpElementFactory;
	}
}