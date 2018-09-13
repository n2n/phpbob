<?php
namespace phpbob\representation\anno;

use n2n\reflection\annotation\Annotation;
use n2n\util\ex\IllegalStateException;

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
	
	public function getPhpTypeDefs();

	public function isEmpty();
	
	public function appendPrependingCode(string $prependingCode = null);
	
	public function determineAnnotation(PhpAnno $phpAnno): ?Annotation;
	
	public function __toString();
}