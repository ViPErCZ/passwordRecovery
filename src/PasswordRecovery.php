<?php

declare(strict_types=1);

namespace Sandbox\PasswordRecovery;

use Nette\Http\IRequest;
use Nette\Localization\Translator;

/**
 * Class PasswordRecovery
 *
 * @package Nextras\PasswordRecovery
 * @author  Martin Chudoba <martin.chudoba@seznam.cz>
 */
class PasswordRecovery
{

    protected UserModelInterface $userRepository;
    protected string $sender;
    protected string $subject;
    protected array|null $smtp;
    protected string $validatorMessage;
    protected string $equalPasswordMessage;
    protected string $emptyPasswordMessage;
    protected int $minimalPasswordLength;
    protected int $expirationTime;
    protected string $submitButton;
    protected string $errorMessage;
    protected string|null $templatePath;
    protected Translator $translator;
    protected IRequest $httpRequest;
    protected Closure|null $passwordGenerator;

    public function __construct(
        string $sender,
        string $subject,
        UserModelInterface $userRepository,
        IRequest $httpRequest
    ) {
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
    public function getEmptyPasswordMessage()
    {
        return $this->emptyPasswordMessage;
    }

    /**
     * @param string $emptyPasswordMessage
     */
    public function setEmptyPasswordMessage($emptyPasswordMessage)
    {
        $this->emptyPasswordMessage = $emptyPasswordMessage;
    }

    /**
     * @return int
     */
    public function getMinimalPasswordLength()
    {
        return $this->minimalPasswordLength;
    }

    public function setMinimalPasswordLength($minimalPasswordLength)
    {
        $this->minimalPasswordLength = (int)$minimalPasswordLength;
    }

    /**
     * @return int
     */
    public function getExpirationTime()
    {
        return $this->expirationTime;
    }

    /**
     * @param int $expirationTime
     */
    public function setExpirationTime($expirationTime)
    {
        if ($expirationTime > 59) {
            $expirationTime = 59;
        }
        $this->expirationTime = $expirationTime;
    }

    /**
     * @return string
     */
    public function getEqualPasswordMessage()
    {
        return $this->equalPasswordMessage;
    }

    /**
     * @param string $equalPasswordMessage
     */
    public function setEqualPasswordMessage($equalPasswordMessage)
    {
        $this->equalPasswordMessage = $equalPasswordMessage;
    }

    /**
     * @param string $validatorMessage
     */
    public function setValidatorMessage($validatorMessage)
    {
        $this->validatorMessage = $validatorMessage;
    }

    /**
     * @param string $submitButton
     */
    public function setSubmitButton($submitButton)
    {
        $this->submitButton = $submitButton;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param array $smtp
     */
    public function setSmtp(array $smtp)
    {
        $this->smtp = $smtp;
    }

    /**
     * @param string $templatePath
     */
    public function setTemplatePath(string $templatePath)
    {
        $this->templatePath = $templatePath;
    }

    /**
     * @return ResetFormDialog
     */
    public function createDialog()
    {
        return new ResetFormDialog($this);
    }

    /**
     * @param $email
     * @param $newPassword
     * @return true|string
     */
    protected function saveNewPassword($email, $newPassword)
    {
        return $this->userRepository->resetPassword($email, $newPassword);
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return array|null
     */
    public function getSmtp()
    {
        return $this->smtp;
    }

    /**
     * @return string
     */
    public function getValidatorMessage()
    {
        return $this->validatorMessage;
    }

    /**
     * @return string
     */
    public function getSubmitButton()
    {
        return $this->submitButton;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getTemplatePath(): ?string
    {
        return $this->templatePath;
    }

    /**
     * @return ITranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @return callable
     */
    public function getPasswordGenerator()
    {
        return $this->passwordGenerator;
    }

    /**
     * @return UserModelInterface
     */
    public function getUserRepository()
    {
        return $this->userRepository;
    }

    /**
     * @return IRequest
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }
}
