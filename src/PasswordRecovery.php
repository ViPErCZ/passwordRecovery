<?php
/**
 * Created by PhpStorm.
 * User: viper
 * Date: 17.1.16
 * Time: 7:54
 */

namespace Sandbox\PasswordRecovery;
use Nette\Http\IRequest;
use Nette\Localization\ITranslator;

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
	protected $equalPasswordMessage;

	/** @var string */
	protected $emptyPasswordMessage;

	/** @var integer */
	protected $minimalPasswordLength;

	/** @var integer */
	protected $expirationTime;

	/** @var string */
	protected $submitButton;

	/** @var string */
	protected $errorMessage;

	/** @var string */
	protected $templatePath;

	/** @var ITranslator */
	protected $translator;

	/** @var IRequest */
	protected $httpRequest;

	/**
	 * PasswordRecovery constructor.
	 * @param $sender
	 * @param $subject
	 * @param IUserModel $userRepository
	 * @param IRequest $httpRequest
	 */
	public function __construct($sender, $subject, IUserModel $userRepository, IRequest $httpRequest) {
		$this->sender = $sender;
		$this->subject = $subject;
		$this->smtp = null;
		$this->passwordGenerator = null;
		$this->userRepository = $userRepository;
		$this->httpRequest = $httpRequest;
	}

	/**
	 * @return string
	 */
	public function getEmptyPasswordMessage() {
		return $this->emptyPasswordMessage;
	}

	/**
	 * @param string $emptyPasswordMessage
	 */
	public function setEmptyPasswordMessage($emptyPasswordMessage) {
		$this->emptyPasswordMessage = $emptyPasswordMessage;
	}

	/**
	 * @return int
	 */
	public function getMinimalPasswordLength() {
		return intval($this->minimalPasswordLength);
	}

	/**
	 * @param int $minimalPasswordLength
	 */
	public function setMinimalPasswordLength($minimalPasswordLength) {
		$this->minimalPasswordLength = intval($minimalPasswordLength);
	}

	/**
	 * @return int
	 */
	public function getExpirationTime() {
		return $this->expirationTime;
	}

	/**
	 * @param int $expirationTime
	 */
	public function setExpirationTime($expirationTime) {
		if ($expirationTime > 59) {
			$expirationTime = 59;
		}
		$this->expirationTime = $expirationTime;
	}

	/**
	 * @return string
	 */
	public function getEqualPasswordMessage() {
		return $this->equalPasswordMessage;
	}

	/**
	 * @param string $equalPasswordMessage
	 */
	public function setEqualPasswordMessage($equalPasswordMessage) {
		$this->equalPasswordMessage = $equalPasswordMessage;
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
	 * @return ResetFormDialog
	 */
	public function createDialog() {
		return new ResetFormDialog($this);
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
	 * @return string
	 */
	public function getSender() {
		return $this->sender;
	}

	/**
	 * @return string
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * @return array|null
	 */
	public function getSmtp() {
		return $this->smtp;
	}

	/**
	 * @return string
	 */
	public function getValidatorMessage() {
		return $this->validatorMessage;
	}

	/**
	 * @return string
	 */
	public function getSubmitButton() {
		return $this->submitButton;
	}

	/**
	 * @return string
	 */
	public function getErrorMessage() {
		return $this->errorMessage;
	}

	/**
	 * @return string
	 */
	public function getTemplatePath() {
		return $this->templatePath;
	}

	/**
	 * @return ITranslator
	 */
	public function getTranslator() {
		return $this->translator;
	}

	/**
	 * @return callable
	 */
	public function getPasswordGenerator() {
		return $this->passwordGenerator;
	}

	/**
	 * @return IUserModel
	 */
	public function getUserRepository() {
		return $this->userRepository;
	}

	/**
	 * @return IRequest
	 */
	public function getHttpRequest() {
		return $this->httpRequest;
	}

}