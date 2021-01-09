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

namespace Fusio\Cli\Service;

use Fusio\Cli\Deploy\EnvReplacerInterface;
use Fusio\Cli\Deploy\IncludeDirective;
use Fusio\Cli\Deploy\Transformer;
use Fusio\Cli\Deploy\TransformerInterface;
use PSX\Schema\Parser\TypeSchema\ImportResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * The deploy service basically transforms a deploy yaml config into a json 
 * format which is then used by the import service. Also it handles the 
 * database migration
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Deploy
{
    /**
     * @var Import
     */
    private $import;

    /**
     * @param Import $import
     */
    public function __construct(Import $import)
    {
        $this->import = $import;
    }

    /**
     * @param string $yaml
     * @param EnvReplacerInterface $envReplacer
     * @param ImportResolver $importResolver
     * @param string|null $basePath
     * @return Import\Result
     */
    public function deploy(string $yaml, EnvReplacerInterface $envReplacer, ImportResolver $importResolver, $basePath = null)
    {
        $includeDirective = new IncludeDirective($envReplacer);

        $data   = Yaml::parse($envReplacer->replace($yaml), Yaml::PARSE_CUSTOM_TAGS);
        $import = new \stdClass();

        if (empty($basePath)) {
            $basePath = getcwd();
        }

        $transformers = [
            Types::TYPE_ACTION     => $this->newTransformer(Transformer\Action::class, [$includeDirective]),
            Types::TYPE_CONFIG     => $this->newTransformer(Transformer\Config::class, [$includeDirective]),
            Types::TYPE_CONNECTION => $this->newTransformer(Transformer\Connection::class, [$includeDirective]),
            Types::TYPE_CRONJOB    => $this->newTransformer(Transformer\Cronjob::class, [$includeDirective]),
            Types::TYPE_EVENT      => $this->newTransformer(Transformer\Event::class, [$includeDirective]),
            Types::TYPE_PLAN       => $this->newTransformer(Transformer\Plan::class, [$includeDirective]),
            Types::TYPE_RATE       => $this->newTransformer(Transformer\Rate::class, [$includeDirective]),
            Types::TYPE_ROUTE      => $this->newTransformer(Transformer\Route::class, [$includeDirective]),
            Types::TYPE_SCHEMA     => $this->newTransformer(Transformer\Schema::class, [$includeDirective, $importResolver]),
            Types::TYPE_SCOPE      => $this->newTransformer(Transformer\Scope::class, [$includeDirective]),
        ];

        // resolve includes
        foreach ($transformers as $type => $transformer) {
            if (isset($data[$type])) {
                $data[$type] = $includeDirective->resolve($data[$type], $basePath, $type);
            }
        }

        // run transformer
        foreach ($transformers as $type => $transformer) {
            /** @var TransformerInterface $transformer */
            $transformer->transform($data, $import, $basePath);
        }

        // import definition
        return $this->import->import(json_encode($import));
    }

    private function newTransformer(string $class, array $arguments = []): TransformerInterface
    {
        $transformer = new $class(...$arguments);
        if (!$transformer instanceof TransformerInterface) {
            throw new \InvalidArgumentException('Transformer must be an instance of ' . TransformerInterface::class);
        }

        return $transformer;
    }
}
