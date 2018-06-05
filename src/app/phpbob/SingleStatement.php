<?php
namespace phpbob;

class SingleStatement extends PhpStatementAdapter {
	private $content;
	
	public function __construct($content) {
		$this->content = $content;
	}
	
	public function getCode() {
		return implode('', $this->getCodeLines());
	}
	
	public function __toString() {
		return $this->content . Phpbob::SINGLE_STATEMENT_STOP; 
	}
}