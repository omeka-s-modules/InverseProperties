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

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(
            null,
            [
                'InverseProperties\Controller\Admin\Index',
                'InverseProperties\Controller\Admin\ResourceTemplate',
            ]
        );
    }

    public function install(ServiceLocatorInterface $services)
    {
        $conn = $services->get('Omeka\Connection');
        $sql = <<<'SQL'
CREATE TABLE inverse_properties_inverse (id INT UNSIGNED AUTO_INCREMENT NOT NULL, resource_template_id INT NOT NULL, resource_template_property_id INT NOT NULL, inverse_property_id INT NOT NULL, INDEX IDX_4251737F16131EA (resource_template_id), INDEX IDX_4251737F2A6B767B (resource_template_property_id), INDEX IDX_4251737F4B4BCE2E (inverse_property_id), UNIQUE INDEX UNIQ_4251737F16131EA2A6B767B (resource_template_id, resource_template_property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE inverse_properties_inverse ADD CONSTRAINT FK_4251737F16131EA FOREIGN KEY (resource_template_id) REFERENCES resource_template (id) ON DELETE CASCADE;
ALTER TABLE inverse_properties_inverse ADD CONSTRAINT FK_4251737F2A6B767B FOREIGN KEY (resource_template_property_id) REFERENCES resource_template_property (id) ON DELETE CASCADE;
ALTER TABLE inverse_properties_inverse ADD CONSTRAINT FK_4251737F4B4BCE2E FOREIGN KEY (inverse_property_id) REFERENCES property (id) ON DELETE CASCADE;
SQL;
        $conn = $services->get('Omeka\Connection');
        $conn->exec('SET FOREIGN_KEY_CHECKS=0;');
        $conn->exec($sql);
        $conn->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function uninstall(ServiceLocatorInterface $services)
    {
        $conn = $services->get('Omeka\Connection');
        $conn->exec('DROP TABLE IF EXISTS inverse_properties_inverse;');
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Omeka\Api\Adapter\ItemAdapter',
            'api.create.post',
            [$this, 'setInversePropertyValues']
        );
        $sharedEventManager->attach(
            'Omeka\Api\Adapter\ItemAdapter',
            'api.update.post',
            [$this, 'setInversePropertyValues']
        );
        $sharedEventManager->attach(
            'Omeka\Api\Adapter\ItemSetAdapter',
            'api.create.post',
            [$this, 'setInversePropertyValues']
        );
        $sharedEventManager->attach(
            'Omeka\Api\Adapter\ItemSetAdapter',
            'api.update.post',
            [$this, 'setInversePropertyValues']
        );
        $sharedEventManager->attach(
            'Omeka\Api\Adapter\MediaAdapter',
            'api.update.post',
            [$this, 'setInversePropertyValues']
        );

        // Pass the "inverse_properties_set_inverses" flag with update and batch
        // update requests. This signals the operation to set inverse property
        // values.
        $controllers = [
            'Omeka\Controller\Admin\Item',
            'Omeka\Controller\Admin\ItemSet',
            'Omeka\Controller\Admin\Media',
        ];
        foreach ($controllers as $controller) {
            $sharedEventManager->attach(
                $controller,
                'view.edit.form.after',
                function (Event $event) {
                    echo '<input type="hidden" name="inverse_properties_set_inverses" value="1">';
                }
            );
        }
        $sharedEventManager->attach(
            'Omeka\Form\ResourceBatchUpdateForm',
            'form.add_elements',
            function (Event $event) {
                $form = $event->getTarget();
                $form->add([
                    'type' => 'hidden',
                    'name' => 'inverse_properties_set_inverses',
                    'attributes' => [
                        'value' => '1',
                        'data-collection-action' => 'replace',
                    ],
                ]);
            }
        );
        $sharedEventManager->attach(
            'Omeka\Api\Adapter\ItemAdapter',
            'api.preprocess_batch_update',
            function (Event $event) {
                $data = $event->getParam('data');
                $rawData = $event->getParam('request')->getContent();
                if (isset($rawData['inverse_properties_set_inverses'])) {
                    $data['inverse_properties_set_inverses'] = $rawData['inverse_properties_set_inverses'];
                }
                $event->setParam('data', $data);
            }
        );
    }

    public function setInversePropertyValues(Event $event)
    {
        $request = $event->getParam('request');

        // Set inverse property values only if the "inverse_properties_set_inverses"
        // flag is passed. This is mainly needed because a batch update request
        // calls batchUpdate() for every collectionAction set to the request,
        // which could result in more than one inverse value being set.
        $setInverses = (bool) $request->getValue('inverse_properties_set_inverses', false);
        if (!$setInverses) {
            return;
        }
        $resource = $event->getParam('response')->getContent();
        $this->getServiceLocator()
            ->get('InverseProperties\InverseProperties')
            ->setInversePropertyValues($resource);
    }
}
