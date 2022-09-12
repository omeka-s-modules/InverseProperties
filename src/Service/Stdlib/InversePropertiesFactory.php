<?php
namespace InverseProperties\Service\Stdlib;

use Interop\Container\ContainerInterface;
use InverseProperties\Stdlib\InverseProperties;
use Zend\ServiceManager\Factory\FactoryInterface;

class InversePropertiesFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new InverseProperties($services);
    }
}
