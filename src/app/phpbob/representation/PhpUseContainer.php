<?php
namespace phpbob\representation;

interface PhpUseContainer extends PhpFileElement {
	public function resolvePhpTypeDefs();
}