<?php
namespace InverseProperties\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use InverseProperties\ControllerPlugin\InverseProperties;
use Zend\ServiceManager\Factory\FactoryInterface;

class InversePropertiesFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new InverseProperties($services);
    }
}
