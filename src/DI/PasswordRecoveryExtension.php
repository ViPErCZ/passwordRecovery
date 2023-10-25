<?php

declare(strict_types=1);

namespace Sandbox\PasswordRecovery\DI;

use Nette\Configurator;
use Nette\DI\CompilerExtension;
use Nette\Localization\Translator;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Sandbox\PasswordRecovery\DTO\Smtp;
use Sandbox\PasswordRecovery\PasswordRecovery;

/**
 * Class PasswordRecoveryExtension
 *
 * @package Nextras\PasswordRecovery\DI
 * @author  Martin Chudoba <martin.chudoba@seznam.cz>
 */
class PasswordRecoveryExtension extends CompilerExtension
{
    private const PASSWORD_RECOVERY = 'passwordRecovery';

    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'sender'                => Expect::string()->required(),
            'subject'               => Expect::string()->required(),
            'smtp'                  => Expect::structure([
                'host'       => Expect::string()->required(),
                'email'      => Expect::string()->required(),
                'password'   => Expect::string()->required(),
                'encryption' => Expect::string(),
            ]),
            'templatePath'          => Expect::string(),
            'validatorMessage'      => Expect::string()->required()->default('Prosím vložte validní heslo.'),
            'submitButton'          => Expect::string()->required()->default('Obnovit heslo'),
            'errorMessage'          => Expect::string()->required()->default('Nové heslo se nepodařilo odeslat. Zkuste to prosím znovu.'),
            'equalPasswordMessage'  => Expect::string()->required()->default('Hesla se musí shodovat.'),
            'emptyPasswordMessage'  => Expect::string()->required()->default('Heslo musí osabhovat alespoň %d znaků'),
            'minimalPasswordLength' => Expect::int(6)->required(),
            'expirationTime'        => Expect::int(10)->required(),
        ]);
    }

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        $config = (array)$this->getConfig();

        $passwordRecovery = $builder->addDefinition($this->prefix(self::PASSWORD_RECOVERY))
            ->setType(PasswordRecovery::class)
            ->setFactory(PasswordRecovery::class)
            ->setArguments([$config['sender'], $config['subject']])
            ->addSetup('$service->setValidatorMessage(?)', [$config['validatorMessage']])
            ->addSetup('$service->setSubmitButton(?)', [$config['submitButton']])
            ->addSetup('$service->setErrorMessage(?)', [$config['errorMessage']])
            ->addSetup('$service->setEqualPasswordMessage(?)', [$config['equalPasswordMessage']])
            ->addSetup('$service->setEmptyPasswordMessage(?)', [$config['emptyPasswordMessage']])
            ->addSetup('$service->setMinimalPasswordLength(?)', [$config['minimalPasswordLength']])
            ->addSetup('$service->setExpirationTime(?)', [$config['expirationTime']])
            ->setAutowired();

        if (isset($config['smtp']) && is_array($config['smtp'])) {
            $smtp = new Smtp($config['smtp']['host'], $config['smtp']['email'], $config['smtp']['password']);
            $passwordRecovery->addSetup('$service->setSmtp(?)', [$smtp]);
        }

        if (isset($config['templatePath']) && is_array($config['templatePath'])) {
            $passwordRecovery->addSetup('$service->setTemplatePath(?)', [$config['templatePath']]);
        }
    }

    public function beforeCompile(): void
    {
        $container = $this->getContainerBuilder();

        $translator = $container->getByType(Translator::class);
        /** @var ServiceDefinition $passwordRecovery */
        $passwordRecovery = $container->getDefinition($this->prefix(self::PASSWORD_RECOVERY));

        if ($translator) {
            $passwordRecovery->addSetup('$service->setTranslator(?)', ['@' . $translator]);
        }
    }
}
