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
        $conn->exec('CREATE TABLE inverse_properties_property_pair (id INT UNSIGNED AUTO_INCREMENT NOT NULL, p1_id INT NOT NULL, p2_id INT NOT NULL, INDEX IDX_A0057456EE679434 (p1_id), INDEX IDX_A0057456FCD23BDA (p2_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;');
        $conn->exec('ALTER TABLE inverse_properties_property_pair ADD CONSTRAINT FK_A0057456EE679434 FOREIGN KEY (p1_id) REFERENCES property (id) ON DELETE CASCADE;');
        $conn->exec('ALTER TABLE inverse_properties_property_pair ADD CONSTRAINT FK_A0057456FCD23BDA FOREIGN KEY (p2_id) REFERENCES property (id) ON DELETE CASCADE;');
    }

    public function uninstall(ServiceLocatorInterface $services)
    {
        $conn = $services->get('Omeka\Connection');
        $conn->exec('DROP TABLE IF EXISTS inverse_properties_property_pair;');
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
    }
}
