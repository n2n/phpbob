<?php
namespace phpbob\representation;

interface PhpFileElement {
	public function __toString();
	public function getPhpTypeDefs() : array;
}