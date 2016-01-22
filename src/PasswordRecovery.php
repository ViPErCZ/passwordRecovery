<?php
/**
 * Created by PhpStorm.
 * User: viper
 * Date: 17.1.16
 * Time: 7:54
 */

namespace Sandbox\PasswordRecovery;
use Latte\Engine;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Mail\SmtpMailer;
use Nette\Utils\Random;

/**
 * Class PasswordRecovery
 * @package Nextras\PasswordRecovery
 * @author Martin Chudoba <martin.chudoba@seznam.cz>
 */
class PasswordRecovery {

	/** @var IUserModel */
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
	 * PasswordRecovery constructor.
	 * @param $sender
	 * @param $subject
	 * @param IUserModel $userRepository
	 */
	public function __construct($sender, $subject, IUserModel $userRepository) {
		$this->sender = $sender;
		$this->subject = $subject;
		$this->smtp = null;
		$this->userRepository = $userRepository;
	}

	/**
	 * @param string $validatorMessage
	 */
	public function setValidatorMessage($validatorMessage) {
		$this->validatorMessage = $validatorMessage;
	}

	/**
	 * @param string $submitButton
	 */
	public function setSubmitButton($submitButton) {
		$this->submitButton = $submitButton;
	}

	/**
	 * @param string $errorMessage
	 */
	public function setErrorMessage($errorMessage) {
		$this->errorMessage = $errorMessage;
	}

	/**
	 * @param ITranslator $translator
	 */
	public function setTranslator(ITranslator $translator) {
		$this->translator = $translator;
	}

	/**
	 * @param array $smtp
	 */
	public function setSmtp(array $smtp) {
		$this->smtp = $smtp;
	}

	/**
	 * @param string $templatePath
	 */
	public function setTemplatePath($templatePath) {
		$this->templatePath = $templatePath;
	}

	/**
	 * @return Form
	 */
	public function createForm() {
		$form = new Form();

		$form->getElementPrototype()->class = "ajax";
		$form->addText("email", "Email:")->setAttribute("placeholder", "Email...")
			->addRule(Form::EMAIL, $this->translator ? $this->translator->translate($this->validatorMessage) : $this->validatorMessage)
			->isRequired();
		$form->addSubmit("recover", $this->translator ? $this->translator->translate($this->submitButton) : $this->submitButton);

		$email = $this->sender;
		$form->onSuccess[] = function(Form $form) use ($email) {
			$newPassword = Random::generate();
			$email = $form->getValues()['email'];
			if ($this->saveNewPassword($email, $newPassword)) {
				try {
					$this->sendNewPasswordToEmail($email, $newPassword);
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
	 * @param $email
	 * @param $newPassword
	 * @return true|string
	 */
	protected function saveNewPassword($email, $newPassword) {
		return $this->userRepository->resetPassword($email, $newPassword);
	}

	/**
	 * @param $email
	 * @param $newPassword
	 * @throws \Exception
	 * @throws \Throwable
	 */
	protected function sendNewPasswordToEmail($email, $newPassword) {
		$message = new Message();
		$message->setFrom($this->sender);
		$message->setSubject($this->subject);
		$message->addTo($email);

		if (is_file($this->templatePath)) {
			$latte = new Engine();
			$params = array(
				'newPassword' => $newPassword
			);
			$message->setHtmlBody($latte->renderToString($this->templatePath, $params));
		} else {
			$message->setBody("Nové heslo: " . $newPassword);
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

}