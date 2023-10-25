<?php

declare(strict_types=1);

namespace Sandbox\PasswordRecovery;

use Latte\Engine;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Mail\SmtpMailer;
use Nette\Utils\Random;
use \Sandbox\PasswordRecovery\DTO\Smtp;

/**
 * Class ResetFormDialog
 *
 * @package Sandbox\PasswordRecovery
 * @author  Martin Chudoba <martin.chudoba@seznam.cz>
 */
class ResetFormDialog extends Control
{

    /** @var PasswordRecovery */
    protected $passwordRecovery;

    /** @var UserModelInterface */
    protected $userRepository;

    /** @var string */
    protected $sender;

    /** @var string */
    protected $subject;

    /** @var null|Smtp */
    protected $smtp;

    /** @var string */
    protected $validatorMessage;

    /** @var string */
    protected $submitButton;

    /** @var string */
    protected $errorMessage;

    /** @var string */
    protected $templatePath;

    /** @var Translator */
    protected $translator;

    /**
     * @var string
     * @persistent
     */
    public $token;

    public function __construct(PasswordRecovery $passwordRecovery)
    {
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

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . "/template/default.latte");

        $template->render();
    }

    protected function sendResetLinkToEmail(string $email): void
    {
        $message = new Message();
        $message->setFrom($this->sender);
        $message->setSubject($this->subject);
        $message->addTo($email);

        if (null !== $this->templatePath && is_file($this->templatePath)) {
            $latte = new Engine();
            $params = [
                'url' => $this->generateResetUrl($email)
            ];
            $message->setHtmlBody($latte->renderToString($this->templatePath, $params));
        } else {
            $message->setBody("Odkaz pro reset hesla: " . $this->generateResetUrl($email));
        }

        $mailer = $this->smtp ? new SmtpMailer([
            'host'     => $this->smtp->host,
            'username' => $this->smtp->email,
            'password' => $this->smtp->password,
            'secure'   => $this->smtp->encryption,
        ]) : new SendmailMailer();
        $mailer->send($message);
    }

    protected function generateResetUrl(string $email): string
    {
        $baseUrl = $this->passwordRecovery->getHttpRequest()->getUrl()->getHostUrl();
        $token = Random::generate(24);
        $signal = $this->link("this", ['token' => $token]);
        $this->userRepository->saveToken($email, $token);

        return $baseUrl . $signal;
    }

    public function getResetForm(): Form
    {
        return $this['resetForm'];
    }

    public function getNewPasswordForm(): Form
    {
        return $this['newPasswordForm'];
    }

    public function isTokenValid(): bool
    {
        if ($this->token) {
            return $this->userRepository->isTokenValid($this->token, $this->passwordRecovery->getExpirationTime());
        }

        return false;
    }

    protected function createComponentResetForm(): Form
    {
        $form = new Form();

        $form->getElementPrototype()->class = "ajax";
        $form->addText("email", "Email:")->setAttribute("placeholder", "Email...")
            ->addRule(Form::EMAIL, $this->translator ? $this->translator->translate($this->validatorMessage) : $this->validatorMessage)
            ->setRequired(true);
        $form->addSubmit("recover", $this->translator ? $this->translator->translate($this->submitButton) : $this->submitButton);

        $form->onSuccess[] = function (Form $form) {
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

    protected function createComponentNewPasswordForm(): Form
    {
        $form = new Form();

        $required = sprintf($this->passwordRecovery->getEmptyPasswordMessage(), $this->passwordRecovery->getMinimalPasswordLength());
        $form->addPassword("pass1", $this->translator ? $this->translator->translate("Nové heslo") : "Nové heslo:")
            ->setRequired($this->translator ? $this->translator->translate($required) : $required)
            ->addRule(Form::MIN_LENGTH, $this->translator ? $this->translator->translate($required) : $required,
                $this->passwordRecovery->getMinimalPasswordLength());
        $form->addPassword("pass2", $this->translator ? $this->translator->translate("Heslo pro kontrolu") : "Heslo pro kontrolu:")
            ->setRequired(true)
            ->addRule(Form::EQUAL
                ,
                $this->translator ? $this->translator->translate($this->passwordRecovery->getEqualPasswordMessage()) : $this->passwordRecovery->getEqualPasswordMessage()
                , $form['pass1']);

        $form->addSubmit("recover", $this->translator ? $this->translator->translate($this->submitButton) : $this->submitButton);

        $form->onSuccess[] = function (Form $form) {
            try {
                $this->userRepository->resetPassword($this->token, $form->getValues()['pass1']);
            } catch (\Exception $exception) {
                $form->addError($exception->getMessage());
            }
        };

        return $form;
    }
}
