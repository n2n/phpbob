<?php
namespace phpbob\representation;

interface PhpFileElement {
	public function onNameChange(\Closure $closure);
}