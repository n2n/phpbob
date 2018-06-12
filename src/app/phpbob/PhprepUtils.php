<?php
namespace phpbob;

use n2n\util\StringUtils;
use n2n\reflection\ArgUtils;
use phpbob\representation\PhpProperty;
use phpbob\representation\PhpConst;
use phpbob\representation\PhpMethod;
use phpbob\representation\PhpParam;
use phpbob\representation\PhpNamespace;
use phpbob\representation\PhpUse;
use phpbob\representation\PhpClass;
use n2n\reflection\annotation\AnnotationSet;
use phpbob\analyze\PhpAnnoAnalyzer;
use phpbob\representation\PhpInterface;
use phpbob\representation\PhpTrait;
use n2n\util\ex\NotYetImplementedException;
use phpbob\analyze\PhpSourceAnalyzingException;
use phpbob\representation\PhpFile;

class PhprepUtils {
	public static function typeNameToPath($typeName) {
		return str_replace(Phpbob::NAMESPACE_SEPERATOR, DIRECTORY_SEPARATOR, trim($typeName, Phpbob::NAMESPACE_SEPERATOR)) 
				. Phpbob::PHP_FILE_EXTENSION;
	}
	
	public static function extractClassName(string $typeName) {
		if (false === $pos = mb_strrpos($typeName, Phpbob::NAMESPACE_SEPERATOR)) {
			return $typeName;
		}
		
		return mb_substr($typeName, $pos + 1); 
	}
	
	public static function isInRootNamespace(string $typeName) {
		return mb_strrpos($typeName, Phpbob::NAMESPACE_SEPERATOR) === 0;
	}
	
	public static function extractNamespace(string $typeName) {
		$lastPos = strrpos($typeName, Phpbob::NAMESPACE_SEPERATOR);
		if (false === $lastPos) return null;
		
		return mb_substr($typeName, 0, $lastPos);
	}
	
	public static function explodeTypeName(string $typename) {
		return explode(Phpbob::NAMESPACE_SEPERATOR, $typename);	
	}
	
	public static function isClassStatement(PhpStatement $phpStatement) {
		if (!($phpStatement instanceof StatementGroup)) return false;
		
		return preg_match('/^(' . preg_quote(Phpbob::KEYWORD_FINAL) .  '\s+)?(' 
				. preg_quote(Phpbob::KEYWORD_ABSTRACT) . '\s+)?'
				. preg_quote(Phpbob::KEYWORD_CLASS). '/i', ltrim($phpStatement->getCode()));
	}
	
	public static function isTypeStatement(PhpStatement $phpStatement) {
		return self::isClassStatement($phpStatement) || self::isInterfaceStatement($phpStatement) 
				|| self::isTraitStatement($phpStatement);
	}
	
	public static function isInterfaceStatement(PhpStatement $phpStatement) {
		if (!($phpStatement instanceof StatementGroup)) return false;
		
		return StringUtils::startsWith(Phpbob::KEYWORD_INTERFACE, 
				ltrim(strtolower($phpStatement->getCode())));
	}
	
	public static function isTraitStatement(PhpStatement $phpStatement) {
		if (!($phpStatement instanceof StatementGroup)) return false;
		
		return StringUtils::startsWith(Phpbob::KEYWORD_TRAIT, 
				ltrim(strtolower($phpStatement->getCode())));
	}
	
	public static function isPropertyStatement(PhpStatement $phpStatement) {
		return $phpStatement instanceof SingleStatement 
				&& preg_match('/(' . preg_quote(Phpbob::CLASSIFIER_PRIVATE) . 
						'|' . preg_quote(Phpbob::CLASSIFIER_PROTECTED) .
						'|' . preg_quote(Phpbob::CLASSIFIER_PUBLIC) . ')\s+(' 
						. preg_quote(Phpbob::KEYWORD_STATIC) . '\s+)?' . 
						preg_quote(Phpbob::VARIABLE_PREFIX) . '/i', $phpStatement->getCode());
	}
	
	public static function isConstStatement(PhpStatement $phpStatement) {
		return $phpStatement instanceof SingleStatement 
				&& StringUtils::startsWith(Phpbob::KEYWORD_CONST, ltrim(strtolower($phpStatement->getCode())));
	}
	
	public static function isAnnotationStatement(PhpStatement $phpStatement) {
		return self::isMethodStatement($phpStatement) 
				&& preg_match('/private\s+static\s+function\s+_annos.*\(.*\)/i', 
						$phpStatement->getCode());
	}
	
	public static function isMethodStatement(PhpStatement $phpStatement) {
		return !!preg_match('/' . preg_quote(Phpbob::KEYWORD_FUNCTION) 
				. '.*\(.*\)/i', $phpStatement->getCode());
	}
	
	public static function isNamespaceStatement(PhpStatement $phpStatement) {
		return $phpStatement instanceof SingleStatement 
				&& preg_match('/^\s*' . preg_quote(Phpbob::KEYWORD_NAMESPACE) . '\s+/i', $phpStatement->getCode());
	}
	
	public static function isUseStatement(PhpStatement $phpStatement) {
		return $phpStatement instanceof SingleStatement 
				&& StringUtils::startsWith(Phpbob::KEYWORD_USE, ltrim(strtolower(implode(' ', $phpStatement->getCodeLines()))));
	}
	
	public static function isTraitUseStatement(PhpStatement $phpStatement) {
		return StringUtils::startsWith(Phpbob::KEYWORD_USE, ltrim(strtolower($phpStatement->getCode())));
	}
	
	public static function isString($value) {
		return StringUtils::startsWith(Phpbob::STRING_LITERAL_SEPERATOR, $value) 
				|| StringUtils::startsWith(Phpbob::STRING_LITERAL_ALTERNATIVE_SEPERATOR, $value);
	}
	

	
	public static function createPhpInterface(PhpStatement $phpStatement,
			PhpStatement $namespaceStatement = null, array $useStatements = null,
			array $statmentsBefore = null) {
	
		ArgUtils::assertTrue($phpStatement instanceof StatementGroup 
				&& self::isInterfaceStatement($phpStatement));
	
		
		$codeParts = self::determineCodeParts($phpStatement);
		//shift "interface"
		array_shift($codeParts);
		
		$interfaceName = array_shift($codeParts);
		
		$phpInterface = new PhpInterface($interfaceName);
		$phpInterface->setPrependingCode(implode('', (array) $statmentsBefore) . 
		    self::createPrependingCode($phpStatement));


		if (null !== $namespaceStatement) {
			$phpInterface->setNamespace(self::createPhpNamespace($namespaceStatement));
		}
	
		$inExtendsClause = false;
	
		foreach ($codeParts as $codePart) {
			
			if ($inExtendsClause) {
				$phpInterface->addInterfaceName($codePart);
				continue;
			}
				
			switch (strtolower($codePart)) {
				case Phpbob::KEYWORD_EXTENDS:
					$inExtendsClause = true;
					continue;
			}
		}
	
		foreach ($useStatements as $useStatement) {
			$phpInterface->addUse(self::createPhpUse($useStatement));
		}
	
		foreach ($phpStatement->getPhpStatements() as $childPhpStatement) {
			if (self::isConstStatement($childPhpStatement)) {
				$phpInterface->addConstant(self::createPhpConst($childPhpStatement));
				continue;
			} elseif (self::isMethodStatement($childPhpStatement)) {
				$phpInterface->addMethod(self::createPhpMethod($childPhpStatement, true));
				continue;
			}
	
			throw new NotYetImplementedException();
		}
	
		return $phpInterface;
	}
	
	public static function createPhpTrait(PhpStatement $phpStatement,
			PhpStatement $namespaceStatement = null, array $useStatements = null,
			array $statmentsBefore = null) {
	
		ArgUtils::assertTrue($phpStatement instanceof StatementGroup && 
				self::isTraitStatement($phpStatement));
	
		$codeParts = self::determineCodeParts($phpStatement);
		//shift "trait"
		array_shift($codeParts);
	
		$traitName = array_shift($codeParts);
		$phpTrait = new PhpTrait($traitName);

		$phpTrait->setPrependingCode(implode('', (array) $statmentsBefore) . 
				(string) self::createPrependingCode($phpStatement));
		
		if (null !== $namespaceStatement) {
			$phpTrait->setNamespace(self::createPhpNamespace($namespaceStatement));
		}
	
		foreach ($useStatements as $useStatement) {
			$phpTrait->addUse(self::createPhpUse($useStatement));
		}
	
		foreach ($phpStatement->getPhpStatements() as $childPhpStatement) {
			
			if (self::isConstStatement($childPhpStatement)) {
				$phpTrait->addConstant(self::createPhpConst($childPhpStatement));
				continue;
			} elseif (self::isPropertyStatement($childPhpStatement)) {
				$phpTrait->addProperty(self::createPhpProperty($childPhpStatement));
				continue;
			} elseif (self::isMethodStatement($childPhpStatement)) {
				$phpTrait->addMethod(self::createPhpMethod($childPhpStatement));
				continue;
			} elseif (self::isTraitUseStatement($childPhpStatement)) {
				$phpTrait->appendTraitNames(self::extractTraitNames($childPhpStatement));
				continue;
			}

			throw new NotYetImplementedException();
		}
		return $phpTrait;
	}
	
	public static function extractTraitNames(PhpStatement $phpStatement) {
		ArgUtils::assertTrue(self::isTraitUseStatement($phpStatement));
		$codeParts = self::determineCodeParts($phpStatement);
		//shift use 
		array_shift($codeParts);
		
		$traitNames = array();
		
		foreach ($codeParts as $codePart) {
			foreach (array_filter(explode(',', $codePart)) as $traitName) {
				$traitNames[$traitName] = $traitName;
			}
		}
		
		return $traitNames;
	}
	
	public static function createPhpMethod(PhpStatement $phpStatement, $abstract = false) {
		ArgUtils::assertTrue(self::isMethodStatement($phpStatement));
		$parts = preg_split('/[\(:]/', trim($phpStatement->getCode()), 3);
		if (count($parts) > 3) {
			throw new \InvalidArgumentException();
		}

		$signaturePart = $parts[0];
		
		$parameterSignaturPart = preg_replace('/\)((?!\)).)*$/', '', $parts[1]);
		
		$returnType = null;
		if (count($parts) > 2) {
			$returnType = preg_replace('/;$/', '', trim($parts[2]));
		}
		
		$classifier = null;
		$final = false;
		$static = false;
		$methodName = null;
		
		foreach (self::explodeByWhiteSpaces($signaturePart) as $part) {
			if (null !== $methodName) break;
			switch (strtolower($part)) {
				case Phpbob::KEYWORD_FUNCTION:
					break;
				case Phpbob::CLASSIFIER_PRIVATE:
				case Phpbob::CLASSIFIER_PROTECTED:
				case Phpbob::CLASSIFIER_PUBLIC:
					$classifier = $part;
					break;
				case Phpbob::KEYWORD_ABSTRACT:
					$abstract = true;
					break;
				case Phpbob::KEYWORD_FINAL:
					$final = true;
					break;
				case Phpbob::KEYWORD_STATIC:
					$static = true;
					break;
				default:
					$methodName = $part;
			}
		}

		$params = array();
		
		foreach (preg_split('/\s*,\s*/', $parameterSignaturPart) as $parameter) {
			$parameterParts = self::determineCodePartsForString(str_replace('=', '', $parameter));
			
			if (count($parameterParts) > 3) {
				throw new \InvalidArgumentException('Invalid Number of Parameter Parts in Parameter: ' . $parameter . ' in Method :' . $phpStatement );
			}
			
			$parameterName = null;
			$typeName = null;
			$value = null;
			$splat = false;
			
			foreach ($parameterParts as $parameterPart) {
				if (StringUtils::startsWith(Phpbob::SPLAT_INDICATOR, $parameterPart)) {
					$splat = true;
					if (StringUtils::endsWith(Phpbob::SPLAT_INDICATOR, $parameterPart)) continue;
					
					$parameterPart = substr($parameterPart, strlen(Phpbob::SPLAT_INDICATOR));
				}
				if (StringUtils::startsWith(Phpbob::VARIABLE_PREFIX, $parameterPart)) {
					$parameterName = $parameterPart;
					continue;
				}
				if (null === $parameterName) {
					$typeName = $parameterPart;
				} else {
					$value = $parameterPart;
				}
			}
			
			if (null === $parameterName) continue;
			$params[] = new PhpParam(self::purifyPropertyName($parameterName), $value, $typeName, $splat);
		}
		
		$phpMethod = new PhpMethod($methodName, $params, $classifier);
		$phpMethod->setAbstract($abstract);
		
		if ($abstract) {
			ArgUtils::assertTrue($phpStatement instanceof SingleStatement);
		} else {
			ArgUtils::assertTrue($phpStatement instanceof StatementGroup);
			$phpMethod->setMethodCode($phpStatement->getStatementsString());
		}
		$phpMethod->setFinal($final);
		$phpMethod->setPrependingCode(self::createPrependingCode($phpStatement));
		$phpMethod->setStatic($static);
		$phpMethod->setReturnType($returnType);
		
		return $phpMethod;
	}
	
	public static function determinSetterMethodName($propertyName) {
		return 'set' . ucfirst((string) $propertyName);
	}

	public static function determineGetterMethodName($propertyName, $boolean = false) {
		return (($boolean) ? 'is' : 'get') . ucfirst((string) $propertyName);
	}
	
	public static function purifyPropertyName($propertyName) {
		return str_replace(Phpbob::VARIABLE_PREFIX, '', $propertyName);
	}
	
	public static function removeLeadingWhiteSpaces($string) {
		return preg_replace('/^\s*/', '', (string) $string);
	}
	
	public static function removeTrailingWhiteSpaces($string) {
		return preg_replace('/\s*$/', '', (string) $string);
	}
	
	public static function trimWhiteSpaces($string) {
		return preg_replace('/(^\s*|^\s*)/', '', (string) $string);
	}
	
	private static function explodeByWhiteSpaces($string) {
		return preg_split('/\s+/', $string, null, PREG_SPLIT_NO_EMPTY);
	}
	
	private static function createPrependingCode(PhpStatement $phpStatement) {
		return implode(PHP_EOL, $phpStatement->getNonCodeLines());
	}
	
	private static function isClassifier($s) {
		return preg_match('/^(private|protected|public)$/i');
	}
}