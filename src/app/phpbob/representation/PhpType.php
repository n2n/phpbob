<?php
namespace phpbob\representation;

interface PhpType {
	public function determineTypeName($name);
	public function isTrait();
	public function isInterface();
	public function isClass();
	public function getNamespace();
	public function getName();
	public function getTypeName();
	public function extractUse(string $typeName): string;
}