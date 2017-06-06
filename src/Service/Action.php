<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\Exception\FactoryResolveException;
use Fusio\Impl\Table;
use Fusio\Engine\Factory;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Action
{
    /**
     * @var \Fusio\Impl\Table\Action
     */
    protected $actionTable;

    /**
     * @var \Fusio\Impl\Table\Routes\Action
     */
    protected $routesActionTable;

    /**
     * @var \Fusio\Impl\Table\Routes\Method
     */
    protected $routesMethodTable;

    /**
     * @var string
     */
    protected $actionEngine;

    public function __construct(Table\Action $actionTable, Table\Routes\Action $routesActionTable, Table\Routes\Method $routesMethodTable, Factory\ActionInterface $actionFactory, $actionEngine)
    {
        $this->actionTable       = $actionTable;
        $this->routesActionTable = $routesActionTable;
        $this->routesMethodTable = $routesMethodTable;
        $this->actionFactory     = $actionFactory;
        $this->actionEngine      = $actionEngine;
    }

    public function create($name, $class, $engine, $config)
    {
        // check whether action exists
        $condition  = new Condition();
        $condition->equals('status', Table\Action::STATUS_ACTIVE);
        $condition->equals('name', $name);

        $action = $this->actionTable->getOneBy($condition);

        if (!empty($action)) {
            throw new StatusCode\BadRequestException('Action already exists');
        }

        if (empty($engine)) {
            $engine = $this->actionEngine;
        }

        // check source
        $this->assertSource($class, $engine);

        // create action
        $this->actionTable->create([
            'status' => Table\Action::STATUS_ACTIVE,
            'name'   => $name,
            'class'  => $class,
            'engine' => $engine,
            'config' => $config ?: null,
            'date'   => new \DateTime(),
        ]);
    }

    public function update($actionId, $name, $class, $engine, $config)
    {
        $action = $this->actionTable->get($actionId);

        if (!empty($action)) {
            if ($action['status'] == Table\Action::STATUS_DELETED) {
                throw new StatusCode\GoneException('Action was deleted');
            }

            // in case the class is empty use the existing class
            if (empty($class)) {
                $class = $action->class;
            }

            if (empty($engine)) {
                $engine = $action->engine;
            }

            // check source
            $this->assertSource($class, $engine);

            // update action
            $this->actionTable->update([
                'id'     => $action->id,
                'name'   => $name,
                'class'  => $class,
                'engine' => $engine,
                'config' => $config ?: null,
                'date'   => new \DateTime(),
            ]);
        } else {
            throw new StatusCode\NotFoundException('Could not find action');
        }
    }

    public function delete($actionId)
    {
        $action = $this->actionTable->get($actionId);

        if (!empty($action)) {
            if ($action['status'] == Table\Action::STATUS_DELETED) {
                throw new StatusCode\GoneException('Action was deleted');
            }

            // check depending
            if ($this->routesMethodTable->hasAction($actionId)) {
                throw new StatusCode\BadRequestException('Cannot delete action because a route depends on it');
            }

            $this->actionTable->update([
                'id'     => $action->id,
                'status' => Table\Action::STATUS_DELETED,
            ]);
        } else {
            throw new StatusCode\NotFoundException('Could not find action');
        }
    }

    /**
     * Checks whether the provided class is resolvable. Note the class can also
     * be a file
     * 
     * @param string $class
     * @param string $engine
     */
    private function assertSource($class, $engine)
    {
        if (!class_exists($engine)) {
            throw new StatusCode\BadRequestException('Could not resolve engine');
        }

        try {
            $action = $this->actionFactory->factory($class, $engine);
        } catch (FactoryResolveException $e) {
            throw new StatusCode\BadRequestException($e->getMessage());
        }

        if (!$action instanceof ActionInterface) {
            throw new StatusCode\BadRequestException('Could not resolve action');
        }
    }
}
