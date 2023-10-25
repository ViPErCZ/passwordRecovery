<?php

declare(strict_types=1);

namespace Sandbox\PasswordRecovery;

use Nette\Http\IRequest;
use Nette\Localization\Translator;
use Sandbox\PasswordRecovery\DTO\Smtp;

/**
 * Class PasswordRecovery
 *
 * @package Nextras\PasswordRecovery
 * @author  Martin Chudoba <martin.chudoba@seznam.cz>
 */
class PasswordRecovery
{
    protected Smtp|null $smtp = null;
    protected Translator|null $translator = null;
    protected Closure|null $passwordGenerator = null;
    protected string $validatorMessage;
    protected string $equalPasswordMessage;
    protected string $emptyPasswordMessage;
    protected int $minimalPasswordLength;
    protected int $expirationTime;
    protected string $submitButton;
    protected string $errorMessage;
    protected string|null $templatePath = null;

    public function __construct(
        protected string $sender,
        protected string $subject,
        protected UserModelInterface $userRepository,
        protected IRequest $httpRequest
    ) {
    }

    public function getEmptyPasswordMessage(): string
    {
        return $this->emptyPasswordMessage;
    }

    public function setEmptyPasswordMessage(string $emptyPasswordMessage)
    {
        $this->emptyPasswordMessage = $emptyPasswordMessage;
    }

    public function getMinimalPasswordLength(): int
    {
        return $this->minimalPasswordLength;
    }

    public function setMinimalPasswordLength(int $minimalPasswordLength): void
    {
        $this->minimalPasswordLength = $minimalPasswordLength;
    }

    public function getExpirationTime(): int
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(int $expirationTime)
    {
        if ($expirationTime > 59) {
            $expirationTime = 59;
        }
        $this->expirationTime = $expirationTime;
    }

    public function getEqualPasswordMessage(): string
    {
        return $this->equalPasswordMessage;
    }

    public function setEqualPasswordMessage(string $equalPasswordMessage)
    {
        $this->equalPasswordMessage = $equalPasswordMessage;
    }

    public function setValidatorMessage(string $validatorMessage)
    {
        $this->validatorMessage = $validatorMessage;
    }

    public function setSubmitButton(string $submitButton)
    {
        $this->submitButton = $submitButton;
    }

    public function setErrorMessage(string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function setSmtp(Smtp $smtp): void
    {
        $this->smtp = $smtp;
    }

    public function setTemplatePath(string $templatePath)
    {
        $this->templatePath = $templatePath;
    }

    public function createDialog(): ResetFormDialog
    {
        return new ResetFormDialog($this);
    }

    protected function saveNewPassword(string $email, string $newPassword): void
    {
        $this->userRepository->resetPassword($email, $newPassword);
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getSmtp(): ?Smtp
    {
        return $this->smtp;
    }

    public function getValidatorMessage(): string
    {
        return $this->validatorMessage;
    }

    public function getSubmitButton(): string
    {
        return $this->submitButton;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getTemplatePath(): ?string
    {
        return $this->templatePath;
    }

    public function getTranslator(): ?Translator
    {
        return $this->translator;
    }

    public function getPasswordGenerator(): ?Closure
    {
        return $this->passwordGenerator;
    }

    public function getUserRepository(): UserModelInterface
    {
        return $this->userRepository;
    }

    public function getHttpRequest(): IRequest
    {
        return $this->httpRequest;
    }
}
