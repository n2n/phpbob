<?php
namespace phpbob\representation\anno;

use phpbob\representation\anno\PhpAnnotationSet;

interface PhpAnnoCollection {
	public function getPhpAnnotationSet(): PhpAnnotationSet;
	/**
	 * @param string $typeName
	 * @return bool
	 */
	public function hasPhpAnno(string $typeName);
	
	/**
	 * @param string $typeName
	 * @throws UnknownElementException
	 * @return PhpAnno
	 */
	public function getPhpAnno(string $typeName);
	
	/**
	 * @return PhpAnno []
	 */
	public function getPhpAnnos();
	
	/**
	 * @param string $typeName
	 * @param string $value
	 * @throws IllegalStateException
	 * @return PhpAnno
	 */
	public function createPhpAnno(string $typeName, string $localName = null);
	
	/**
	 * @param string $typeName
	 * @return \phpbob\representation\anno\PhpAnnoAdapter
	 */
	public function removePhpAnno(string $typeName);
	
	public function resetPhpAnnos();
	
	public function getAnnotationString();
	
	public function getPhpTypeDefs();

	public function isEmpty();
	
	public function appendPrependingCode(string $prependingCode = null);
}