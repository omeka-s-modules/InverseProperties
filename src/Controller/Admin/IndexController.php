<?php
namespace InverseProperties\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $propertyPairs = $this->params()->fromPost('property_pairs', []);
            $this->inverseProperties()->setPropertyPairs($propertyPairs);
            $this->messenger()->addSuccess('Property pairs successfully updated'); // @translate
        }
        $propertyPairs = $this->inverseProperties()->getPropertyPairs();

        $view = new ViewModel;
        $view->setVariable('propertyPairs', $propertyPairs);
        return $view;
    }
}
