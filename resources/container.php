<?php

use Fusio\Cli\Deploy\EnvReplacer;
use Fusio\Cli\Deploy\EnvReplacerInterface;
use Fusio\Cli\Service\Authenticator;
use Fusio\Cli\Service\Client;
use Fusio\Cli\Service\Deploy;
use Fusio\Cli\Service\Export;
use Fusio\Cli\Service\Import;
use Fusio\Cli\Transport\Http;
use Fusio\Cli\Transport\TransportInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(Authenticator::class);
    $services->set(Client::class);
    $services->set(Deploy::class);
    $services->set(Export::class);
    $services->set(Import::class);

    $services->set(Http::class);
    $services->alias(TransportInterface::class, Http::class);

    $services->set(EnvReplacer::class);
    $services->alias(EnvReplacerInterface::class, EnvReplacer::class);

    $services->load('Fusio\\Cli\\Command\\', __DIR__ . '/../src/Command');

};
