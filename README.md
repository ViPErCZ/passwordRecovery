#Password recovery extension for Nette Framework
======

Requirements
------------

Sandbox/PasswordRecovery requires PHP 8.1.0 or higher.

- [Nette Framework](https://github.com/nette/nette)


Installation
------------

The best way to install Sandbox/PasswordRecovery is using  [Composer](http://getcomposer.org/):

```sh
$ composer require sandbox/passwordrecovery
```


Configuration
-------------
Then you have to register extension in config.neon.
```php
extensions:
    passwordRecovery: Sandbox\PasswordRecovery\DI\PasswordRecoveryExtension
```
and configuration section in config.neon:
```php
passwordRecovery:
    passwordRecovery:
    sender: "sandbox@domain.net"
    subject: "Obnova hesla"
    submitButton: "Obnovit heslo"
    validatorMessage: "Prosím vložte validní heslo."
    equalPasswordMessage: "Hesla se musí shodovat."
    emptyPasswordMessage: "Heslo musí obsahovat alespoň %d znaků"
    minimalPasswordLength: 6
    expirationTime: 45 #minute, max 59
    errorMessage: "Odkaz pro obnovu hesla se nepodařilo odeslat. Zkuste to prosím znovu."
    smtp: [127.0.0.1, info@domain.tld, password]
```

Next, you need to register a service that implements an interface IUserModel.
Example is sandbox project: [https://github.com/ViPErCZ/sandbox](https://github.com/ViPErCZ/sandbox)

Usage
------------
Sample using in Presenter:
```php
/**
 * @return \Nette\Application\UI\Form
*/
protected function createComponentRecovery() {
	$control = $this->passwordRecovery->createDialog();
	$control->getResetForm()->onSuccess[] = function(Form $form) {
		$this->flashMessage('Odkaz pro obnovu hesla byl odeslán na Váš email ' . $form->getValues()['email'] . ".");
		$this->redrawControl('recoveryForm');
	};

	$control->getResetForm()->onError[] = function() {
		$this->redrawControl('recoveryForm');
	};

	$control->getNewPasswordForm()->onSuccess[] = function() {
		$this->flashMessage('Heslo bylo úspěšně nastaveno. Pokračujte na přihlašovací obrazovku.');
		if ($this->isAjax()) {
           $this->redrawControl('recoveryForm');
        } else {
           $this->redirect('Home:default');
        }
	};

	$control->getNewPasswordForm()->onError[] = function() {
		if ($this->isAjax()) {
           $this->redrawControl('recoveryForm');
        } else {
           $this->redirect('Home:default');
        }
	};

	return $control;
}
```
and template has default template (using twitter bootstrap class)
```php
{snippet recoveryForm}
	<div n:foreach="$flashes as $flash" class="alert alert-success">{$flash->message}</div>
	{if count($flashes) == 0}
		{control recovery}
	{/if}
{/snippet}
```
If you want to use your custom template, set the variable **templatePath** to the path to the latte template.

Extension using Nette\Localization\Translator and all configurated strings are translated.

-----

Homepage [https://packagist.org/packages/vipercz/sandbox](https://packagist.org/packages/vipercz/sandbox) and repository [https://github.com/ViPErCZ/passwordRecovery](https://github.com/ViPErCZ/passwordRecovery).
