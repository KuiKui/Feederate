<?php

namespace Feederate\FeederateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class FrontController extends Controller
{
    /**
     * Index action
     *
     * @Route("/")
     */
    public function indexAction()
    {
        $securityContext = $this->get('security.context');

        if ($securityContext->isGranted('ROLE_ADMIN')) {
            return $this->appAction();
        } else if ($securityContext->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')) {
            return $this->homeAction();
        }
    }

    protected function homeAction()
    {
        return $this->render('FeederateFeederateBundle:Front:home.html.twig');
    }

    protected function appAction()
    {
        return $this->render('FeederateFeederateBundle:Front:app.html.twig');
    }
}
