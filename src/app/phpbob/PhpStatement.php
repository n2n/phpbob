<?php
namespace phpbob;

interface PhpStatement {
	public function __toString();
	public function getLines();
	public function getCode();
	public function getCodeLines();
	public function getNonCodeLines();
}