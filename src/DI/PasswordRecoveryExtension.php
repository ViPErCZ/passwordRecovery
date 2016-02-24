<?php
/**
 * Created by PhpStorm.
 * User: viper
 * Date: 17.1.16
 * Time: 7:37
 */

namespace Sandbox\PasswordRecovery\DI;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\Utils\Validators;

/**
 * Class PasswordRecoveryExtension
 * @package Nextras\PasswordRecovery\DI
 * @author Martin Chudoba <martin.chudoba@seznam.cz>
 */
class PasswordRecoveryExtension extends CompilerExtension {

	/** @var array */
	public $defaults = array(
		"sender" 				=> null,
		"subject"				=> null,
		"smtp"					=> null,
		"templatePath"			=> null,
		"validatorMessage"		=> "Prosím vložte validní heslo.",
		"submitButton"			=> "Obnovit heslo",
		"errorMessage"			=> "Nové heslo se nepodařilo odeslat. Zkuste to prosím znovu.",
		"equalPasswordMessage"	=> "Hesla se musí shodovat.",
		"emptyPasswordMessage"	=> "Heslo musí osabhovat alespoň %d znaků",
		"minimalPasswordLength"	=> 6,
		"expirationTime"		=> 10
	);

	/**
	 * @throws \Nette\Utils\AssertionException
	 */
	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		Validators::assert($config['sender'], 'string', 'Password recovery sender email');
		Validators::assert($config['subject'], 'string', 'Password recovery subject email');

		$passwordRecovery = $builder->addDefinition($this->prefix('passwordRecovery'))
			->setClass('Sandbox\PasswordRecovery\PasswordRecovery')
			->setArguments(array($config['sender'], $config['subject']))
			->addSetup('$service->setValidatorMessage(?)', array($config['validatorMessage']))
			->addSetup('$service->setSubmitButton(?)', array($config['submitButton']))
			->addSetup('$service->setErrorMessage(?)', array($config['errorMessage']))
			->addSetup('$service->setEqualPasswordMessage(?)', array($config['equalPasswordMessage']))
			->addSetup('$service->setEmptyPasswordMessage(?)', array($config['emptyPasswordMessage']))
			->addSetup('$service->setMinimalPasswordLength(?)', array($config['minimalPasswordLength']))
			->addSetup('$service->setExpirationTime(?)', array($config['expirationTime']))
			->setInject(false);

		if (isset($config['smtp']) && is_array($config['smtp'])) {
			$passwordRecovery->addSetup('$service->setSmtp(?)', array($config['smtp']));
		}

		if (isset($config['templatePath']) && is_array($config['templatePath'])) {
			$passwordRecovery->addSetup('$service->setTemplatePath(?)', array($config['templatePath']));
		}
	}

	/**
	 *
	 */
	public function beforeCompile() {
		$container = $this->getContainerBuilder();

		$translator = $container->getByType('Nette\Localization\ITranslator');
		$passwordRecovery = $container->getDefinition($this->prefix('passwordRecovery'));

		if ($translator) {
			$passwordRecovery->addSetup('$service->setTranslator(?)', array('@' . $translator));
		}
	}


	/**
	 * @param Configurator $configurator
	 */
	public static function register(Configurator $configurator) {
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('passwordRecovery', new PasswordRecoveryExtension());
		};
	}
}