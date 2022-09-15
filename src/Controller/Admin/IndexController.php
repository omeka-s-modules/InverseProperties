<?php
namespace InverseProperties\Controller\Admin;

use InverseProperties\Stdlib\InverseProperties;
use Laminas\Form\Form;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    protected $inverseProperties;

    public function __construct(InverseProperties $inverseProperties)
    {
        $this->inverseProperties = $inverseProperties;
    }

    public function resourceTemplatesAction()
    {
        $resourceTemplates = $this->api()->search('resource_templates', ['sort_by' => 'label'])->getContent();

        $view = new ViewModel;
        $view->setVariable('resourceTemplates', $resourceTemplates);
        return $view;
    }

    public function propertiesAction()
    {
        $resourceTemplateId = $this->params('resource-template-id');
        $resourceTemplate = $this->inverseProperties->getEntity('Omeka\Entity\ResourceTemplate', $resourceTemplateId);
        $inverseProperties = $this->inverseProperties->getInverseProperties($resourceTemplateId);
        $inversePropertyIds = [];
        foreach ($inverseProperties as $inverseProperty) {
            $resourceTemplatePropertyId = $inverseProperty->getResourceTemplateProperty()->getId();
            $propertyId = $inverseProperty->getProperty()->getId();
            $inversePropertyIds[$resourceTemplatePropertyId] = $propertyId;
        }

        // Must use a generic form for CSRF protection.
        $form = $this->getForm(Form::class);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            $inversePropertyIds = $this->params()->fromPost('inverse_property_ids', []);
            if ($form->isValid()) {
                $this->inverseProperties->setInverseProperties($resourceTemplateId, $inversePropertyIds);
                $this->messenger()->addSuccess('Inverse properties successfully updated'); // @translate
                return $this->redirect()->toRoute(null, [], true);
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('resourceTemplate', $resourceTemplate);
        $view->setVariable('inversePropertyIds', $inversePropertyIds);
        return $view;
    }
}
