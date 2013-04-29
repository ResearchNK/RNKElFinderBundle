<?php

namespace RNK\RNKElFinderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ElFinderController extends Controller
{
    /**
     * @Template()
     */
    public function showAction()
    {
        return array();
    }

    public function backendAction()
    {
        $connector = $this->container->get('rnk_el_finder.connector');
        return $connector->connect();
    }
}
