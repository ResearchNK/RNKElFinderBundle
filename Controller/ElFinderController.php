<?php

namespace RNK\ElFinderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

class ElFinderController extends Controller
{
    /**
     * @Route("/rnk_el_finder_show", name="rnk_el_finder_show")
     * @Template()
     */
    public function showAction()
    {
        return array();
    }
    
    /**
     * @Route("/rnk_el_finder_backend", name="rnk_el_finder_backend")
     */
    public function backendAction()
    {
        $connector = $this->container->get('rnk_el_finder.connector');
        return $connector->connect();
    }
}
