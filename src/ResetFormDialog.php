<?php
/**
 * Created by PhpStorm.
 * User: viper
 * Date: 22.2.16
 * Time: 13:51
 */

namespace Sandbox\PasswordRecovery;

use Latte\Engine;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Mail\SmtpMailer;
use Nette\Utils\Random;

/**
 * Class ResetFormDialog
 * @package Sandbox\PasswordRecovery
 * @author Martin Chudoba <martin.chudoba@seznam.cz>
 */
class ResetFormDialog extends Control {

	/** @var PasswordRecovery */
	protected $passwordRecovery;

	/** @var UserModelInterface */
	protected $userRepository;

	/** @var string */
	protected $sender;

	/** @var string */
	protected $subject;

	/** @var null|array */
	protected $smtp;

	/** @var string */
	protected $validatorMessage;

	/** @var string */
	protected $submitButton;

	/** @var string */
	protected $errorMessage;

	/** @var string */
	protected $templatePath;

	/** @var ITranslator */
	protected $translator;

	/**
	 * @var string
	 * @persistent
	 */
	public $token;

	/**
	 * ResetFormDialog constructor.
	 * @param $passwordRecovery
	 */
	public function __construct(PasswordRecovery $passwordRecovery) {
		$this->passwordRecovery = $passwordRecovery;
		$this->translator = $passwordRecovery->getTranslator();
		$this->validatorMessage = $passwordRecovery->getValidatorMessage();
		$this->submitButton = $passwordRecovery->getSubmitButton();
		$this->sender = $passwordRecovery->getSender();
		$this->templatePath = $passwordRecovery->getTemplatePath();
		$this->smtp = $passwordRecovery->getSmtp();
		$this->subject = $passwordRecovery->getSubject();
		$this->userRepository = $passwordRecovery->getUserRepository();
		$this->errorMessage = $passwordRecovery->getErrorMessage();
	}

	/**
	 *
	 */
	public function render() {
		$template = $this->getTemplate();
		$template->setFile(__DIR__ . "/template/default.latte");

		$template->render();
	}

	/**
	 * @param $email
	 * @throws \Exception
	 * @throws \Throwable
	 */
	protected function sendResetLinkToEmail($email) {
		$message = new Message();
		$message->setFrom($this->sender);
		$message->setSubject($this->subject);
		$message->addTo($email);

		if (is_file($this->templatePath)) {
			$latte = new Engine();
			$params = array(
				'url' => $this->generateResetUrl($email)
			);
			$message->setHtmlBody($latte->renderToString($this->templatePath, $params));
		} else {
			$message->setBody("Odkaz pro reset hesla: " . $this->generateResetUrl($email));
		}

		if ($this->smtp) {
			$mailer = new SmtpMailer(array(
				'host' => $this->smtp[0],
				'username' => $this->smtp[1],
				'password' => $this->smtp[2],
				'secure' => isset($this->smtp[3]) ? $this->smtp[3] : '',
			));
			$mailer->send($message);
		} else {
			$mailer = new SendmailMailer();
			$mailer->send($message);
		}
	}

	/**
	 * @param $email
	 * @return string
	 */
	protected function generateResetUrl($email) {
		$baseUrl = $this->passwordRecovery->getHttpRequest()->getUrl()->getHostUrl();
		$token = Random::generate(24);
		$signal = $this->link("this", array('token' => $token));
		$this->userRepository->saveToken($email, $token);

		return $baseUrl . $signal;
	}

	/**
	 * @return Form
	 */
	public function getResetForm() {
		return $this['resetForm'];
	}

	/**
	 * @return Form
	 */
	public function getNewPasswordForm() {
		return $this['newPasswordForm'];
	}

	/**
	 * @return bool
	 */
	public function isTokenValid() {
		if ($this->token) {
			return $this->userRepository->isTokenValid($this->token, $this->passwordRecovery->getExpirationTime());
		} else {
			return false;
		}
	}

	/**
	 * @return Form
	 */
	protected function createComponentResetForm() {
		$form = new Form();

		$form->getElementPrototype()->class = "ajax";
		$form->addText("email", "Email:")->setAttribute("placeholder", "Email...")
			->addRule(Form::EMAIL, $this->translator ? $this->translator->translate($this->validatorMessage) : $this->validatorMessage)
			->setRequired(true);
		$form->addSubmit("recover", $this->translator ? $this->translator->translate($this->submitButton) : $this->submitButton);

		$form->onSuccess[] = function(Form $form) {
			$email = $form->getValues()['email'];
			if ($this->userRepository->isUserValid($email)) {
				try {
					$this->sendResetLinkToEmail($email);
				} catch (\Exception $e) {
					$form->addError($e->getMessage());
				}
			} else {
				$form->addError($this->translator ? $this->translator->translate($this->errorMessage) : $this->errorMessage);
			}

		};

		return $form;
	}

	/**
	 * @return Form
	 */
	protected function createComponentNewPasswordForm() {
		$form = new Form();

		$required = sprintf($this->passwordRecovery->getEmptyPasswordMessage(), $this->passwordRecovery->getMinimalPasswordLength());
		$form->addPassword("pass1", $this->translator ? $this->translator->translate("Nové heslo") : "Nové heslo:")
			->setRequired($this->translator ? $this->translator->translate($required) : $required)
			->addRule(Form::MIN_LENGTH, $this->translator ? $this->translator->translate($required) : $required, $this->passwordRecovery->getMinimalPasswordLength());
		$form->addPassword("pass2", $this->translator ? $this->translator->translate("Heslo pro kontrolu") : "Heslo pro kontrolu:")
			->setRequired(true)
			->addRule(Form::EQUAL
				, $this->translator ? $this->translator->translate($this->passwordRecovery->getEqualPasswordMessage()) : $this->passwordRecovery->getEqualPasswordMessage()
				, $form['pass1']);

		$form->addSubmit("recover", $this->translator ? $this->translator->translate($this->submitButton) : $this->submitButton);

		$form->onSuccess[] = function(Form $form) {
			$result = $this->userRepository->resetPassword($this->token, $form->getValues()['pass1']);
			if ($result !== true) {
				$form->addError($result);
			}
		};

		return $form;
	}
}
