<?php
namespace InverseProperties\Service\Controller\Admin;

use Interop\Container\ContainerInterface;
use InverseProperties\Controller\Admin\ResourceTemplateController;
use Zend\ServiceManager\Factory\FactoryInterface;

class ResourceTemplateControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $inverseProperties = $services->get('InverseProperties\InverseProperties');
        return new ResourceTemplateController($inverseProperties);
    }
}
