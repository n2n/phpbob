<?php
namespace phpbob\representation;

use phpbob\representation\traits\InterfacesTrait;
use phpbob\representation\ex\UnknownElementException;
use n2n\util\ex\IllegalStateException;

class PhpInterface extends PhpTypeAdapter {
	use InterfacesTrait;
	
	private $phpInterfaceMethods = [];
	
	public function extendsInterface(string $typeName) {
		return $this->hasInterfacePhpTypeDef($typeName);
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpInterfaceMethod(string $name) {
		return isset($this->phpInterfaceMethods[$name]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpInterfaceMethod(string $name) {
		if (!isset($this->phpInterfaceMethods[$name])) {
			throw new UnknownElementException('No interface method with name "' . $name . '" given.');
		}
		
		return $this->phpInterfaceMethods[$name];
	}

	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpInterfaceMethod
	 */
	public function createPhpInterfaceMethod(string $name) {
		$this->checkPhpInterfaceMethodName($name);
		
		$phpInterfaceMethod = new PhpInterfaceMethod($name);
		$that = $this;
		$phpInterfaceMethod->onNameChange(function($oldName, $newName) {
			$that->checkPhpInterfaceMethodName($newName);
			
			$tmpPhpInterfaceMethod = $that->phpInterfaceMethods[$oldName];
			unset($that->phpInterfaceMethods[$oldName]);
			$that->phpInterfaceMethods[$newName] = $tmpPhpInterfaceMethod;
		});
		
		return $phpInterfaceMethod;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 */
	private function checkPhpInterfaceMethodName(string $name) {
		if (isset($this->phpInterfaceMethods[$name])) {
			throw new IllegalStateException('Interface method with name ' . $name . ' already defined.');
		}
	}
}