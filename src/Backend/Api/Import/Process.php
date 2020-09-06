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

namespace Fusio\Impl\Backend\Api\Import;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Model;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Json\Parser;

/**
 * Process
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Process extends BackendApiAbstract
{
    /**
     * @Inject
     * @var \Fusio\Impl\Service\System\Import
     */
    protected $systemImportService;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());

        $post = $builder->addMethod('POST');
        $post->setSecurity(Authorization::BACKEND, ['backend.import']);
        $post->setRequest(Model\Adapter_Extern::class);
        $post->addResponse(200, Model\Import_Response::class);

        return $builder->getSpecification();
    }

    /**
     * {@inheritdoc}
     * @param Model\Adapter_Extern $record
     */
    public function doPost($record, HttpContextInterface $context)
    {
        try {
            $this->connection->beginTransaction();

            $data   = Parser::encode($record);
            $result = $this->systemImportService->import($data);

            $this->connection->commit();

            return [
                'success' => true,
                'message' => 'Import successful',
                'result'  => $result->getLogs(),
            ];
        } catch (\Throwable $e) {
            $this->connection->rollback();

            throw $e;
        }
    }
}
