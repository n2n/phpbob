<?php
namespace phpbob\representation;

interface PhpClassLike extends PhpType {
	public function hasPhpMethod(string $name): bool;
	public function getPhpMethod(string $name): PhpMethod;
	public function getPhpMethods(): array;
	public function createPhpMethod(string $name): PhpMethod;
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
	public function createPhpProperty(string $name, string $classifier): PhpProperty;
	
	
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
	
	public function getPhpTraitUses(): array;
	
	/**
	 * @param string $typeName
	 * @param string $localName
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpTraitUse
	 */
	public function createPhpTraitUse(string $typeName, string $localName = null): PhpTraitUse;
	
}