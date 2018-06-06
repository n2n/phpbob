<?php
namespace phpbob\representation\anno;

class PhpMethodAnno extends PhpAnnoAdapter {
	private $methodName;
	private $methodNameChangeClosures = [];
	
	public function __construct(PhpAnnotationSet $phpAnnotationSet, 
			string $methodName, $prependingCode = null) {
		parent::__construct($phpAnnotationSet, $prependingCode);

		$this->methodName = $methodName;
	}
	
	public function getMethodName() {
		return $this->methodName;
	}
	
	public function setMethodName(string $methodName) {
		if ($this->methodName !== $methodName) {
			$this->triggerMethodNameChange($this->methodName, $methodName);
			$this->methodName = $methodName;
		}
	
		return $this;
	}
	
	public function onMethodNameChange(\Closure $closure) {
		$this->methodNameChangeClosures[] = $closure;
	}
	
	private function triggerMethodNameChange(string $oldMethodName, string $newMethodName) {
		foreach ($this->methodNameChangeClosures as $methodNameChangeClosure) {
			$methodNameChangeClosure($oldMethodName, $newMethodName);
		}
	}
}