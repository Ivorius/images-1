<?php declare(strict_types = 1);

namespace WebChemistry\Images\DI;

use Nette\DI\ServiceDefinition;
use Nette\DI\Statement;
use Nette\StaticClass;
use WebChemistry\Images\Parsers\ModifierParser;
use WebChemistry\Images\Parsers\Values;

class DIHelper {

	use StaticClass;

	public static function addModifiersFromArray(ServiceDefinition $modifiers, array $services): void {
		foreach ($services as $modifier) {
			$modifiers->addSetup('addLoader', [new Statement($modifier)]);
		}
	}

	public static function addAliasesFromArray(ServiceDefinition $modifiers, array $aliases) {
		foreach ($aliases as $alias => $configuration) {
			$parsed = ModifierParser::parse($configuration);
			$parsed = new Statement(Values::class, [$parsed->getValues(), $parsed->getVariables()]);

			$modifiers->addSetup('addAlias', [$alias, $parsed]);
		}
	}

}