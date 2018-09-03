<?php

namespace Logistics\Traits;

use Illuminate\Support\Fluent;

trait MailSenderChangeable
{
    /**
     * @param Fluent $settings
     */
    public function changeMailSender(Fluent $settings)
    {
        $mailTransport = app()->make('mailer')->getSwiftMailer()->getTransport();

        dump($mailTransport instanceof \Swift_Transport);

        if ($mailTransport instanceof \Swift_Transport) {
            /** @var \Swift_Transport $mailTransport */
            $mailTransport->setUsername($settings->mail_username);
            $mailTransport->setPassword($settings->mail_password);
        }
    }
}
