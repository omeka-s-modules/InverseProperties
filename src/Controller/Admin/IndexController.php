<?php
namespace InverseProperties\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $propertyPairs = $this->inverseProperties()->getPropertyPairs();

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            echo '<pre>';print_r($postData);exit;
        }

        $view = new ViewModel;
        $view->setVariable('propertyPairs', [[1,2], [3,4], [5,6]]);
        return $view;
    }
}
