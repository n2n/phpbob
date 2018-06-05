<?php
namespace phpbob\analyze;

use phpbob\Phpbob;
use n2n\util\StringUtils;
use n2n\reflection\ArgUtils;
use phpbob\representation\PhpAnnoParam;

class PhpAnnoParamAnalyzer {

	public function analyze($paramString, array $variableDefinitions) {
		ArgUtils::valArray($variableDefinitions, new \ReflectionClass('phpbob\representation\PhpAnnoParam'));
		$params = array();
	
		foreach ($this->determineFirstLevelParamStrings($paramString) as $annoParamString) {
			if ($this->isNewClass($annoParamString)) {
				$params[] = $this->createNewClassAnnoParam($annoParamString);
				continue;
			}
				
			if ($this->isVariable($annoParamString)) {
				if (!isset($variableDefinitions[$annoParamString])) {
					throw new PhpAnnotationSourceAnalyzingException('Invalid variable . ' . $annoParamString);
				}
	
				$params[] = $variableDefinitions[$annoParamString];
				continue;
			}
				
			throw new PhpSourceAnalyzingException('Invalid anno param string: ' . $annoParamString);
		}
	
		return $params;
	}
	
	private function determineFirstLevelParamStrings($paramString) {
		$level = 0;
		$annoParamStrings = array();
		$annoParamString = '';
		
		foreach (str_split((string) $paramString) as $char) {
			switch ($char) {
				case Phpbob::PARAMETER_GROUP_START:
					$level++;
					break;
				case Phpbob::PARAMETER_GROUP_END:
					$level--;
					break;
				default:
			}
			
			if ($level < 0) {
				throw new PhpSourceAnalyzingException('Invalid param String: ' . $paramString . '. Too many closing groups.');
			}
			
			if ($level === 0 && $char === Phpbob::PARAMETER_SEPERATOR) {
				$annoParamStrings[] = trim($annoParamString);
				$annoParamString = '';
				continue;
			}
			
			$annoParamString .= $char;
		}
		
		if (!StringUtils::isEmpty($annoParamString)) {
			$annoParamStrings[] = trim($annoParamString);
		}
		
		return $annoParamStrings;
	}
	
	public function createNewClassAnnoParam($newClassString) {
		$typeName = null;
		$constructorString = null;
		$level = 0;
		
		$newClassString = preg_replace('/^' . Phpbob::KEYWORD_NEW . '\s+/', '', $newClassString);
		foreach (str_split((string) $newClassString) as $char) {
			switch ($char) {
				case Phpbob::PARAMETER_GROUP_START:
					$level++;
					break;
				case Phpbob::PARAMETER_GROUP_END:
					$level--;
					break;
				default:
					
			}
			
			if ($level < 0) {
				throw new PhpSourceAnalyzingException('Invalid new class String' . $newClassString . '. Too many closing groups.');
			}
			
			if ($level === 0) {
				if ($char === Phpbob::PARAMETER_GROUP_END) continue;

				if (null !== $constructorString) {
					throw new PhpSourceAnalyzingException('invalid new Class statement:' . $newClassString);
				}
				
				$typeName .= $char;
				continue;
			}
			
			if ($level === 1 && $char == Phpbob::PARAMETER_GROUP_START) continue;
			
			$constructorString .= $char;
		}
		
		$param =  new PhpAnnoParam($typeName, $this->buildConstructorParams($constructorString));
		
		return $param;
	}
	
	private function buildConstructorParams($constructorString) {
		$constructorParams = array();
		foreach ($this->determineFirstLevelParamStrings($constructorString) as $constructorStringPart) {
			$constructorParams[] = new SimpleParam($constructorStringPart);
		}
		
		return $constructorParams;
	}
	
	private function isNewClass($string) {
		return StringUtils::startsWith(Phpbob::KEYWORD_NEW, ltrim($string));
	}
	
	private function isVariable($string) {
		return StringUtils::startsWith(Phpbob::VARIABLE_PREFIX, ltrim($string));
	}
}