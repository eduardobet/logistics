<?php

namespace Logistics\Mail;

use Swift_Mailer;
use Swift_SmtpTransport;
use Illuminate\Mail\Mailable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Mail\Mailer;

class ConfigurableMailable extends Mailable
{
    /**
     * Override Mailable functionality to support per-user mail settings
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @return void
     */
    public function send(Mailer $mailer)
    {
        $security = $this->tenant->mail_encryption ?: null;
        $transport = new Swift_SmtpTransport($this->tenant->mail_host, $this->tenant->mail_port, $security);
        $transport->setUsername($this->tenant->mail_username);
        $transport->setPassword($this->tenant->mail_password);
        $mailer->setSwiftMailer(new Swift_Mailer($transport));

        Container::getInstance()->call([$this, 'build']);
        $mailer->send($this->buildView(), $this->buildViewData(), function ($message) {
            $this->buildFrom($message)
                ->buildRecipients($message)
                ->buildSubject($message)
                ->buildAttachments($message)
                ->runCallbacks($message);
        });
    }
}
