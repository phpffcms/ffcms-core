<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Exception\SyntaxException;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mime\Email;

/**
 * Class Mailer. Email send overlay over symfonymailer instance
 * @package Ffcms\Core\Helper
 */
class Mailer
{
    private $mailer;
    private $from;

    private $message;

    /**
     * Mailer constructor. Construct object with ref to symfonymailer and sender info
     * @param \Symfony\Component\Mailer\Mailer $instance
     * @param string $from
     */
    public function __construct(SymfonyMailer $instance, string $from)
    {

        $this->mailer = $instance;
        $this->from = $from;
    }

    /**
     * Factory constructor
     * @param \Symfony\Component\Mailer\Mailer $instance
     * @param string $from
     * @return Mailer
     */
    public static function factory(SymfonyMailer $instance, string $from)
    {
        return new self($instance, $from);
    }

    /**
     * Check if mailer features is enabled
     */
    public function isEnabled(): bool 
    {
        $cfg = App::$Properties->get('mail');
        return (bool)$cfg['enable'];
    }

    /**
     * Set tpl file
     * @param string $tpl
     * @param array|null $params
     * @param null|string $dir
     * @return $this
     */
    public function tpl(string $tpl, ?array $params = [], ?string $dir = null)
    {
        try {
            $this->message = App::$View->render($tpl, $params, $dir);
        } catch (SyntaxException $e) {
        }
        return $this;
    }

    /**
     * Set mail to address
     * @param string $address
     * @param string $subject
     * @param null|string $message
     * @return bool
     */
    public function send(string $address, string $subject, ?string $message = null): bool
    {
        // try to get message from global if not passed direct
        if (!$message) {
            $message = $this->message;
        }

        try {
            if (!$message || Str::likeEmpty($message)) {
                throw new \Exception('Message body is empty!');
            }

            // build message body
            $msg = (new Email())
                ->from($this->from)
                ->to($address)
                ->subject($subject)
                ->html($message);

            // send msg via transporter
            $this->mailer->send($msg);
            return true;
        } catch (\Exception $e) {
            if (App::$Debug) {
                App::$Debug->addException($e);
                App::$Debug->addMessage('Send mail failed! Info: ' . $e->getMessage(), 'error');
            }
            return false;
        }
    }
}
