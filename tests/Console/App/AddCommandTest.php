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

namespace Fusio\Impl\Tests\Console\App;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * AddCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class AddCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        $command = Environment::getService('console')->find('app:add');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'userId' => 1,
            'status' => 1,
            'name' => 'Bar-App',
            'url' => 'http://google.com',
            'parameters' => 'foo=bar&bar=foo',
            'scopes' => 'backend,authorization',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertEquals('App successful created', trim($actual));

        // check app
        $connection = $this->connection->fetchAssoc('SELECT id, user_id, status, name, url, parameters, app_key, app_secret FROM fusio_app ORDER BY id DESC');

        $this->assertEquals(6, $connection['id']);
        $this->assertEquals(1, $connection['user_id']);
        $this->assertEquals(1, $connection['status']);
        $this->assertEquals('Bar-App', $connection['name']);
        $this->assertEquals('http://google.com', $connection['url']);
        $this->assertEquals('foo=bar&bar=foo', $connection['parameters']);
        $this->assertNotEmpty($connection['app_key']);
        $this->assertNotEmpty($connection['app_secret']);
    }
}
