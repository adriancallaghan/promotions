<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PromotionsController extends AbstractActionController
{
    
    public function indexAction()
    {
        // displays page based on
        // /locale/campaign/promotion/
        // passed from router
        
        // renders templates
        $viewModel = new ViewModel();
        $viewModel->setTemplate('application/promotions/two.phtml');
         //echo $this->escapeHtml('article') 
        return $viewModel;
        
        
    }
}
