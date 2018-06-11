<?php
namespace phpbob\representation;

interface PhpFileElement {
	public function getPhpFile();
	public function getPhpTypeDefs() : array;
	public function __toString();
}