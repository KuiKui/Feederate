<?php

namespace Feederate\FeederateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Feederate\FeederateBundle\Form\ImporterType;

class SettingsController extends Controller
{
    /**
     * Index action
     *
     * @Route("/settings")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Import feedbin action
     *
     * @Route("/settings/import")
     * @Template()
     */
    public function importAction(Request $request)
    {
        $form = $this->container->get('form.factory')->createNamed('', new ImporterType());
        $viewParameters['form'] = $form->createView();

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {

                $viewParameters['status'] = $this->get('feederate.importer.importer')
                    ->setPlatform($form['platform']->getData())
                    ->import($form['attachment']->getData()->getPathname());
            }
        }

        return $viewParameters;
    }
}
