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

namespace Fusio\Impl\Factory;

use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Factory\Resolver;

/**
 * EngineDetector
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class EngineDetector
{
    /**
     * This method determines the fitting engine for the provided string. The
     * provided string gets modified in case it has the uri format 
     * 
     * @param string $class
     * @return string
     */
    public static function getEngine(&$class)
    {
        $engine = null;

        if (($pos = strpos($class, '://')) !== false) {
            $proto = substr($class, 0, $pos);

            switch ($proto) {
                case 'file':
                    $class  = substr($class, $pos + 3);
                    $engine = self::getEngineByFile($class);
                    break;

                case 'http':
                case 'https':
                    $engine = Resolver\HttpUrl::class;
                    break;

                default:
                    $class  = substr($class, $pos + 3);
                    $engine = self::getEngineByProto($proto);
            }
        } elseif (is_file($class)) {
            $engine = self::getEngineByFile($class);
        } elseif (class_exists($class)) {
            $engine = PhpClass::class;
        }

        if ($engine === null) {
            $engine = PhpClass::class;
        }

        return $engine;
    }

    /**
     * @param string $file
     * @return string|null
     */
    private static function getEngineByFile($file)
    {
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

        switch ($fileExtension) {
            case 'php':
                return Resolver\PhpFile::class;

            default:
                return Resolver\StaticFile::class;
        }
    }

    /**
     * @param string $proto
     * @return string|null
     */
    private static function getEngineByProto($proto)
    {
        switch ($proto) {
            case 'PhpClass':
                return PhpClass::class;

            case 'PhpFile':
                return Resolver\PhpFile::class;

            case 'HttpUrl':
                return Resolver\HttpUrl::class;

            case 'StaticFile':
                return Resolver\StaticFile::class;
        }

        return null;
    }
}
