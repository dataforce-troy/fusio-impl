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

namespace Fusio\Impl\Consumer\Schema\User;

use PSX\Schema\Property;
use PSX\Schema\SchemaAbstract;

/**
 * Account
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Account extends SchemaAbstract
{
    public function getDefinition()
    {
        $sb = $this->getSchemaBuilder('Consumer User Account');
        $sb->integer('id');
        $sb->integer('status');
        $sb->string('name');
        $sb->string('email');
        $sb->integer('points');
        $sb->arrayType('scopes')
            ->setItems(Property::getString());
        $sb->objectType('attributes')
            ->setTitle('Consumer User Attributes')
            ->setAdditionalProperties(Property::getString());
        $sb->string('date')
            ->setFormat('date-time');

        return $sb->getProperty();
    }
}
