<?php
namespace phpbob\representation;

use phpbob\representation\traits\PrependingCodeTrait;
use phpbob\representation\traits\NameChangeSubjectTrait;
use n2n\util\ex\IllegalStateException;
use phpbob\representation\ex\UnknownElementException;

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
	 * @param string $name
	 * @return \phpbob\representation\PhpConst
	 */
	public function createPhpConst(string $name) {
		$this->checkPhpConstName($name);
		
		$phpConst = new PhpConst($name);
		$that = $this;
		$phpConst->onNameChange(function($oldName, $newName) {
			$that->checkPhpConstName($newName);
			
			$tmpPhpConst = $that->phpConsts[$oldName];
			unset($that->phpConsts[$oldName]);
			$that->phpConsts[$newName] = $tmpPhpConst;
		});
			
		return $phpConst;
	}
	
	private function checkPhpConstName(string $name) {
		if (isset($this->phpConsts[$name])) {
			throw new IllegalStateException('Constant with name ' . $name . ' already defined.');
		}
	}
 }