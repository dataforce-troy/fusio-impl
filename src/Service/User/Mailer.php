<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Mail\MailerInterface;
use Fusio\Impl\Service;

/**
 * Mailer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Mailer
{
    /**
     * @var Service\Config
     */
    private $configService;

    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(Service\Config $configService, MailerInterface $mailer)
    {
        $this->configService = $configService;
        $this->mailer = $mailer;
    }

    public function sendActivationMail($token, $name, $email)
    {
        $this->sendMail('mail_register', $token, $name, $email);
    }

    public function sendResetPasswordMail($token, $name, $email)
    {
        $this->sendMail('mail_pw_reset', $token, $name, $email);
    }

    private function sendMail(string $template, $token, $name, $email)
    {
        $subject = $this->configService->getValue($template . '_subject');
        $body    = $this->configService->getValue($template . '_body');

        $values = array(
            'name'  => $name,
            'email' => $email,
            'token' => $token,
        );

        foreach ($values as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }

        $this->mailer->send($subject, [$email], $body);
    }
}
