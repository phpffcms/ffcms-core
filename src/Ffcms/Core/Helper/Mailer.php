<?php

namespace Ffcms\Core\Helper;
use Ffcms\Core\App;
use Ffcms\Core\Exception\SyntaxException;

/**
 * Class Mailer. Email send overlay over swiftmailer instance
 * @package Ffcms\Core\Helper
 */
class Mailer
{
    /** @var \Swift_Mailer */
    private $swift;
    private $from;

    private $message;
    private $tpl;

    /**
     * Mailer constructor. Construct object with ref to swiftmailer and sender info
     * @param \Swift_Mailer $instance
     * @param string $from
     */
    public function __construct(\Swift_Mailer $instance, string $from)
    {
        $this->swift = $instance;
        $this->from = $from;
    }

    /**
     * Factory constructor
     * @param \Swift_Mailer $instance
     * @param string $from
     * @return Mailer
     */
    public static function factory(\Swift_Mailer $instance, string $from)
    {
        return new self($instance, $from);
    }

    /**
     *
     * @param string $tpl
     * @param array|null $params
     * @param null|string $dir
     * @return $this
     */
    public function tpl(string $tpl, ?array $params = [], ?string $dir = null)
    {
        try {
            $this->message = App::$View->render($tpl, $params, $dir);
        } catch (SyntaxException $e){}
        return $this;
    }

    public function send(string $to, string $subject, ?string $message = null)
    {
        // try to get message from global if not passed direct
        if ($message === null)
            $message = $this->message;

        try {
            if ($message === null)
                throw new \Exception('Message body is empty!');

            // try to build message and send it
            $message = (new \Swift_Message($subject))
                ->setFrom($this->from)
                ->setTo($to)
                ->setBody($message);
            $this->swift->send($message);
        } catch (\Exception $e) {
            if (App::$Debug) {
                App::$Debug->addException($e);
                App::$Debug->addMessage('Send mail failed! Info: ' . $e->getMessage(), 'error');
            }
        }
    }
}