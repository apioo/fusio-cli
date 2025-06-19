<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Cli\Deploy\Transformer;

use Fusio\Cli\Deploy\SchemeBuilder;
use Fusio\Cli\Deploy\TransformerAbstract;
use Fusio\Cli\Service\Import\Types;

/**
 * Cronjob
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Cronjob extends TransformerAbstract
{
    public function transform(array $data, \stdClass $import, ?string $basePath): void
    {
        $cronjob = $data[Types::TYPE_CRONJOB] ?? [];

        if (!empty($cronjob) && is_array($cronjob)) {
            $result = [];
            foreach ($cronjob as $name => $entry) {
                $result[] = $this->transformCronjob($name, $entry, $basePath);
            }
            $import->cronjob = $result;
        }
    }

    protected function transformCronjob(string $name, mixed $data, ?string $basePath): array
    {
        $data = $this->includeDirective->resolve($data, $basePath, Types::TYPE_CRONJOB);
        $data['name'] = $name;

        if (isset($data['action'])) {
            $data['action'] = SchemeBuilder::forAction($data['action']);
        }

        return $data;
    }
}
