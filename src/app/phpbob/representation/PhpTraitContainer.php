<?php
namespace phpbob\representation;

interface PhpTraitContainer extends PhpType {
	public function getTraitNames();
}