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

    public function indexAction()
    {
        // Must use a generic form to get CSRF protection.
        $form = $this->getForm(Form::class);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {
                $propertyPairs = $this->params()->fromPost('property_pairs', []);
                $this->inverseProperties->setPropertyPairs($propertyPairs);
                $this->messenger()->addSuccess('Property pairs successfully updated'); // @translate
                return $this->redirect()->toRoute();
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        // Transform property pair entities to their property IDs.
        $propertyPairs = [];
        foreach ($this->inverseProperties->getPropertyPairs() as $propertyPairEntity) {
            $propertyPairs[] = [
                'p1' => $propertyPairEntity->getP1()->getId(),
                'p2' => $propertyPairEntity->getP2()->getId(),
            ];
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('propertyPairs', $propertyPairs);
        return $view;
    }
}
