<?php
namespace phpbob\representation;

use n2n\util\ex\IllegalStateException;

class PhpNamespaceResolver {
	private $phpElementFactory;
	
	public function __construct(PhpElementFactory $phpElementFactory) {
		$this->phpElementFactory = $phpElementFactory;
	}
	
	public function resolveNamespaces() {
		$this->phpElementFactory->resetPhpUses();
				
		foreach ($this->phpElementFactory->getPhpTypeDefs() as $phpTypeDef) {
			if (!$phpTypeDef->hasTypeName()) continue;
			
			$alias = $phpTypeDef->determineAlias();
			$typeName = $phpTypeDef->getTypeName();
			if (null !== $alias 
					&& $this->phpElementFactory->hasPhpUseAlias($alias)
					&& !$this->phpElementFactory->getPhpUseForAlias($alias)->getTypeName() !== $typeName) {
				throw new IllegalStateException('duplicate alias ' . $alias . ' for use statements given');
			}
			
			if (!$this->phpElementFactory->hasPhpUse($typeName)) {
				$this->phpElementFactory->createPhpUse($typeName, $alias);
				return;
			}
		}
	}
}