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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Table;

/**
 * Register
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Register
{
    /**
     * @var \Fusio\Impl\Service\User
     */
    protected $userService;

    /**
     * @var \Fusio\Impl\Service\User\Captcha
     */
    protected $captchaService;

    /**
     * @var \Fusio\Impl\Service\User\Token
     */
    protected $tokenService;

    /**
     * @var \Fusio\Impl\Service\User\Mailer
     */
    protected $mailerService;

    /**
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

    /**
     * @param \Fusio\Impl\Service\User $userService
     * @param \Fusio\Impl\Service\Config $configService
     * @param \Fusio\Impl\Service\User\Captcha $captchaService
     * @param \Fusio\Impl\Service\User\Token $tokenService
     * @param \Fusio\Impl\Service\User\Mailer $mailerService
     */
    public function __construct(Service\User $userService, Captcha $captchaService, Token $tokenService, Mailer $mailerService, Service\Config $configService)
    {
        $this->userService    = $userService;
        $this->captchaService = $captchaService;
        $this->tokenService   = $tokenService;
        $this->mailerService  = $mailerService;
        $this->configService  = $configService;
    }

    public function register($name, $email, $password, $captcha)
    {
        $this->captchaService->assertCaptcha($captcha);

        // determine initial user status
        $status   = Table\User::STATUS_DISABLED;
        $approval = $this->configService->getValue('user_approval');
        if (!$approval) {
            $status = Table\User::STATUS_CONSUMER;
        }

        $scopes = $this->userService->getDefaultScopes();
        $userId = $this->userService->create(
            $status,
            $name,
            $email,
            $password,
            $scopes,
            UserContext::newAnonymousContext()
        );

        // send activation mail
        if ($approval) {
            $token = $this->tokenService->generateToken($userId);

            $this->mailerService->sendActivationMail($token, $name, $email);
        }
    }
}
