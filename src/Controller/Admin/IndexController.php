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
        $resourceTemplate = $this->api()->read('resource_templates', $resourceTemplateId)->getContent();

        // Must use a generic form for CSRF protection.
        $form = $this->getForm(Form::class);

        if ($this->getRequest()->isPost()) {
            echo '<pre>';print_r($this->params()->fromPost());exit;
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {
                // @todo: save data
                $this->messenger()->addSuccess('Inverse properties successfully updated'); // @translate
                return $this->redirect()->toRoute();
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('resourceTemplate', $resourceTemplate);
        return $view;
    }
}
