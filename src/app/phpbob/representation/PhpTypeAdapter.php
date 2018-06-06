<?php
namespace phpbob\representation;

use phpbob\representation\traits\PrependingCodeTrait;
use phpbob\representation\traits\NameChangeSubjectTrait;
use n2n\util\ex\IllegalStateException;
use phpbob\representation\ex\UnknownElementException;
use phpbob\PhprepUtils;

class PhpTypeAdapter implements PhpType {
	use PrependingCodeTrait;
	use NameChangeSubjectTrait;
	
	private $phpFile;
	private $phpNamespace;
	private $phpConsts = [];
	
	public function __construct(PhpFile $phpFile, string $name, PhpNamespace $phpNamespace = null) {
		$this->phpFile = $phpFile;
		$this->phpNamespace = $phpNamespace;
		$this->name = $name;
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpConst(string $name) {
		return isset($this->phpConsts[$name]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpConst(string $name) {
		if (!isset($this->phpConsts[$name])) {
			throw new UnknownElementException('No constant with name "' . $name . '" given.');
		}
		
		return $this->phpConsts[$name];
	}
	
	/**
	 * @return PhpConst[]
	 */
	public function getPhpConsts() {
		return $this->phpConsts;
	}
	
	/**
	 * @param string $name
	 * @return \phpbob\representation\PhpConst
	 */
	public function createPhpConst(string $name) {
		$this->checkPhpConstName($name);
		
		$phpConst = new PhpConst($name);
		$that = $this;
		$phpConst->onNameChange(function($oldName, $newName) use ($that) {
			$that->checkPhpConstName($newName);
			
			$tmpPhpConst = $that->phpConsts[$oldName];
			unset($that->phpConsts[$oldName]);
			$that->phpConsts[$newName] = $tmpPhpConst;
		});
			
		return $phpConst;
	}
	
	public function removePhpConst(string $name) {
		unset($this->phpConsts[$name]);
		
		return $this;
	}
	
	private function checkPhpConstName(string $name) {
		if (isset($this->phpConsts[$name])) {
			throw new IllegalStateException('Constant with name ' . $name . ' already defined.');
		}
	}
	
	protected function buildConstStr() {
		if (empty($this->phpConsts)) return '';
		
		return PhprepUtils::removeTrailingWhiteSpaces(implode(PHP_EOL, $this->phpConsts)) . PHP_EOL . PHP_EOL;
	}
 }