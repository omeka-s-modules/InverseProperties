<?php
namespace InverseProperties\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->redirect()->toRoute('admin/inverse-properties-resource-template', []);
    }
}
