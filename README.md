#Password recovery extension for Nette Framework
======

Requirements
------------

Sandbox/PasswordRecovery requires PHP 5.4.0 or higher.

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
and configuration section in config.neon (version 1.0.x):
```php
passwordRecovery:
    sender: "sandbox@domain.net"
    subject: "Obnova hesla"
    submitButton: "Obnovit heslo"
    validatorMessage: "Prosím vložte validní heslo."
    errorMessage: "Nové heslo se nepodařilo odeslat. Zkuste to prosím znovu."
    smtp: [127.0.0.1, info@domain.tld, password]
```
and configuration section in config.neon (version 1.1.x):
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
Sample using in Presenter (version 1.0.x):
```php
use Nette\Application\UI\Form;
use Sandbox\PasswordRecovery\PasswordRecovery;

/**
 * Class PassRestorePresenter
 * @package App
 */
class PassRestorePresenter {

	/** @var PasswordRecovery */
	protected $passwordRecovery;

	/**
	 * @param PasswordRecovery $passwordRecovery
	 */
	public function injectPasswordRecovery(PasswordRecovery $passwordRecovery) {
		$this->passwordRecovery = $passwordRecovery;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentRecoveryForm() {
		$form = $this->passwordRecovery->createForm();

		$form->onSuccess[] = function(Form $form) {
			$this->flashMessage('Heslo bylo odesláno na Váš email ' . $form->getValues()['email'] . ".");
			$this->redrawControl('recoveryForm');
		};

		$form->onError[] = function() {
			$this->redrawControl('recoveryForm');
		};

		return $form;
	}
}
```
or version 1.1.x and high
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
and template (version 1.0.x)
```php
{snippet recoveryForm}
		<div n:foreach="$flashes as $flash" class="alert alert-success">{$flash->message}</div>
			{if count($flashes) == 0}
				{form recoveryForm role => "form"}
				{if $form->hasErrors()}
					<div n:foreach="$form->errors as $error" class="alert alert-danger">{$error}</div>
				{/if}
				<div class="row form-group">
					<div class="col-xs-12">
						{label email /}
						{input email class => "form-control", placeholder => "Email..."}
					</div>
				</div>
				<div class="row form-group col-lg-10">
					{input recover class => "btn btn-success"}
				</div>
				{/form}
			{/if}
	{/snippet}
```
and template in version 1.1.x and high has default template (using twitter bootstrap class)
```php
{snippet recoveryForm}
	<div n:foreach="$flashes as $flash" class="alert alert-success">{$flash->message}</div>
	{if count($flashes) == 0}
		{control recovery}
	{/if}
{/snippet}
```
Extension using Nette\Localization\Translator and all configurated strings are translated.

-----

Homepage [https://packagist.org/packages/vipercz/sandbox](https://packagist.org/packages/vipercz/sandbox) and repository [https://github.com/ViPErCZ/passwordRecovery](https://github.com/ViPErCZ/passwordRecovery).
