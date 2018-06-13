<?php
namespace phpbob\representation;

interface PhpType extends PhpNamespaceElement {
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpConst(string $name);
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpConst(string $name);
	
	/**
	 * @return PhpConst[]
	 */
	public function getPhpConsts();
	
	/**
	 * @param string $name
	 * @return \phpbob\representation\PhpConst
	 */
	public function createPhpConst(string $name);
	
	
	/**
	 * @param string $name
	 */
	public function removePhpConst(string $name);
	
	/**
	 * @param string $typeName
	 * @param string $alias
	 * @param string $type
	 * 
	 * @return PhpUse
	 */
	public function createPhpUse(string $typeName, string $alias = null, string $type = null);
	
	
	/**
	 * @param string $localName
	 * 
	 * @return string
	 */
	public function determineTypeName(string $localName);
}