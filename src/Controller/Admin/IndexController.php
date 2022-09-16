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
        if (!$this->userIsAllowed($resourceTemplate, 'update')) {
            return $this->redirect()->toRoute('admin/inverse-properties', [], true);
        }

        // Cache the resource template property ID / inverse property ID pairs.
        $inversePropertyIds = [];
        $inverses = $this->inverseProperties->getInverses($resourceTemplateId);
        foreach ($inverses as $inverse) {
            $resourceTemplateProperty = $inverse->getResourceTemplateProperty();
            $inverseProperty = $inverse->getInverseProperty();
            $inversePropertyIds[$resourceTemplateProperty->getId()] = $inverseProperty->getId();
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
