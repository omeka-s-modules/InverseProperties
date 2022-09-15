<?php
namespace InverseProperties;

use Omeka\Module\AbstractModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include sprintf('%s/config/module.config.php', __DIR__);
    }

    public function install(ServiceLocatorInterface $services)
    {
        $conn = $services->get('Omeka\Connection');
        $sql = <<<'SQL'
CREATE TABLE inverse_properties_inverse_property (id INT UNSIGNED AUTO_INCREMENT NOT NULL, resource_template_id INT NOT NULL, resource_template_property_id INT NOT NULL, property_id INT NOT NULL, INDEX IDX_6FC58AAD16131EA (resource_template_id), INDEX IDX_6FC58AAD2A6B767B (resource_template_property_id), INDEX IDX_6FC58AAD549213EC (property_id), UNIQUE INDEX UNIQ_6FC58AAD16131EA2A6B767B (resource_template_id, resource_template_property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE inverse_properties_inverse_property ADD CONSTRAINT FK_6FC58AAD16131EA FOREIGN KEY (resource_template_id) REFERENCES resource_template (id) ON DELETE CASCADE;
ALTER TABLE inverse_properties_inverse_property ADD CONSTRAINT FK_6FC58AAD2A6B767B FOREIGN KEY (resource_template_property_id) REFERENCES resource_template_property (id) ON DELETE CASCADE;
ALTER TABLE inverse_properties_inverse_property ADD CONSTRAINT FK_6FC58AAD549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE;
SQL;
        $conn = $services->get('Omeka\Connection');
        $conn->exec('SET FOREIGN_KEY_CHECKS=0;');
        $conn->exec($sql);
        $conn->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function uninstall(ServiceLocatorInterface $services)
    {
        $conn = $services->get('Omeka\Connection');
        $conn->exec('DROP TABLE IF EXISTS inverse_properties_inverse_property;');
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Omeka\Api\Adapter\ItemAdapter',
            'api.update.post',
            function (Event $event) {
                $resourceEntity = $event->getParam('response')->getContent();
                $inverseProperties = $this->getServiceLocator()->get('InverseProperties\InverseProperties');
                $inverseProperties->setInversePropertyValues($resourceEntity);
            }
        );
    }
}
