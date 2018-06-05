<?php
namespace phpbob;

class StatementGroup extends PhpStatementAdapter {
	private $startCode;
	private $phpStatements = array();
	private $endCode;
	
	public function __construct($startCode = null) {
		$this->startCode = $startCode;
	}
	public function getLines() {
		return preg_split('/(\\r\\n|\\n|\\r)/', (string) $this->startCode);
	}
	
	public function getStartCode() {
		return $this->startCode;
	}
	
	public function addStatement(PhpStatement $phpStatement) {
		$this->phpStatements[] = $phpStatement;
	}

	public function getPhpStatements() {
		return $this->phpStatements;
	}
	
	public function setEndCode($endCode) {
		$this->endCode = $endCode;
	}
	
	public function getEndCode() {
		return $this->endCode;
	}
	
	public function __toString() {
		if (null === $this->startCode) {
			return $this->getStatementsString();
		}
		
		return $this->startCode . Phpbob::GROUP_STATEMENT_OPEN . 
				$this->getStatementsString() . $this->endCode . Phpbob::GROUP_STATEMENT_CLOSE;
	}
	
	public function getStatementsString() {
		return implode('', $this->phpStatements + array($this->endCode));
	}
}