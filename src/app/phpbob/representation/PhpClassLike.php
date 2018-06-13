<?php
namespace phpbob\representation;

use phpbob\representation\anno\PhpAnnotationSet;
use phpbob\Phpbob;

interface PhpClassLike extends PhpType {
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpMethod(string $name): bool;
	
	/**
	 * @param string $name
	 * @return PhpMethod
	 */
	public function getPhpMethod(string $name): PhpMethod;
	
	/**
	 * @return PhpMethod []
	 */
	public function getPhpMethods(): array;
	
	/**
	 * @param string $name
	 * @return PhpMethod
	 */
	public function createPhpMethod(string $name): PhpMethod;
	
	/**
	 * @param string $name
	 * @return PhpClassLike
	 */
	public function removePhpMethod(string $name): PhpClassLike;
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpProperty(string $name): bool;
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpProperty(string $name): PhpProperty;
	
	/**
	 * @return PhpProperty []
	 */
	public function getPhpProperties(): array;
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpProperty
	 */
	public function createPhpProperty(string $name, string $classifier = Phpbob::CLASSIFIER_PRIVATE): PhpProperty;
	
	/**
	 * @param string $name
	 * @return \phpbob\representation\PhpClassLikeAdapter
	 */
	public function removePhpProperty(string $name): PhpClassLike;
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpTraitUse(string $typeName): bool;
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpTraitUse(string $typeName): PhpTraitUse;
	
	/**
	 * @return PhpTraitUse []
	 */
	public function getPhpTraitUses(): array;
	
	/**
	 * @param string $typeName
	 * @param string $localName
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpTraitUse
	 */
	public function createPhpTraitUse(string $typeName, string $localName = null): PhpTraitUse;
	
	/**
	 * @return PhpAnnotationSet
	 */
	public function getPhpAnnotationSet();
	
	/**
	 * @param string $prependingCode
	 */
	public function setPrependingCode(string $prependingCode = null);
}