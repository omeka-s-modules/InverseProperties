<?php
namespace InverseProperties\Controller\Admin;

use Laminas\Form\Form;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        // Must use a generic form to get CSRF protection.
        $form = $this->getForm(Form::class);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {
                $propertyPairs = $this->params()->fromPost('property_pairs', []);
                $this->inverseProperties()->setPropertyPairs($propertyPairs);
                $this->messenger()->addSuccess('Property pairs successfully updated'); // @translate
                return $this->redirect()->toRoute();
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('propertyPairs', $this->inverseProperties()->getPropertyPairs());
        return $view;
    }
}
