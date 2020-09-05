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

namespace Fusio\Impl\Console\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\OutputWriter;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand as DoctrineMigrateCommand;
use Fusio\Engine\ConnectorInterface;
use Fusio\Impl\Migrations\ConfigurationBuilder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for executing a migration to a specified version or the latest
 * available version
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class MigrateCommand extends DoctrineMigrateCommand
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \Fusio\Engine\ConnectorInterface
     */
    protected $connector;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     * @param \Fusio\Engine\ConnectorInterface $connector
     */
    public function __construct(Connection $connection, ConnectorInterface $connector)
    {
        parent::__construct();

        $this->connection = $connection;
        $this->connector  = $connector;
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('migration:migrate')
            ->setAliases(['install'])
            ->addOption('connection', null, InputOption::VALUE_REQUIRED, 'The connection name to use for this command.')
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        $outputWriter = new OutputWriter(function($message) use ($output) {
            return $output->writeln($message);
        });

        $connectionId = $input->getOption('connection');
        if (empty($connectionId)) {
            $config = ConfigurationBuilder::fromSystem($this->connection, $outputWriter);
        } else {
            $config = ConfigurationBuilder::fromConnector($connectionId, $this->connector, $outputWriter);
        }

        $this->setMigrationConfiguration($config);

        parent::initialize($input, $output);
    }
}
