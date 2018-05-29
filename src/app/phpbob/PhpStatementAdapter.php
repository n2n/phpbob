<?php
namespace phpbob;

abstract class PhpStatementAdapter implements PhpStatement {
	
	private $codeLines = null;
	private $nonCodeLines = null;
	
	public function getLines() {
		return preg_split('/(\\r\\n|\\n|\\r)/', (string) $this);
	}
	/**
	 * 
	 */
	public function getCodeLines() {
		if (null === $this->codeLines) {
			$this->determineLines();
		}
		
		return $this->codeLines;
	}
	
	public function getNonCodeLines() {
		if (null === $this->nonCodeLines) {
			$this->determineLines();
		}
		
		return $this->nonCodeLines;
	}
	
	private function determineLines() {
		$this->codeLines = array();
		$this->nonCodeLines = array();
		$inComment = false;
		
		foreach ($this->getLines() as $line) {

			if ($inComment) {
				if ($this->hasCommentEnd($line)) {
					$inComment = false;
				}
				$this->nonCodeLines[] = $line;
				continue;
			}
			
			if (preg_match('/(^\s*$|^\s*(\/\/|#))/', $line)) {
				$this->nonCodeLines[] = $line;
				continue;
			}
			
			if (preg_match('/^\s*\/\*/', $line)) {
				if (!$this->hasCommentEnd($line)) {
					$inComment = true;
				}
				$this->nonCodeLines[] = $line;
				continue;
			}
			$this->codeLines[] = $line;
		}
	} 
	
	private function hasCommentEnd($string) {
		return preg_match('/\*\//', $string);
	}
	
	public function getCode() {
		return implode(' ', $this->getCodeLines());
	}
}
