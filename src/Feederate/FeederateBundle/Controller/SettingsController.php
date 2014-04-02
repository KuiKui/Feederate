<?php

namespace Feederate\FeederateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
     * Import OPML action
     *
     * @Route("/settings/import")
     * @Template()
     */
    public function importAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('attachment', 'file')
            ->getForm();

        $twigParams = array('form' => $form->createView());

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                $filename = $form['attachment']->getData()->getPathname();
                $twigParams['status'] = $this
                    ->get('feederate.importer.importer')
                    ->import($filename);
            }
        }

        return $twigParams;
    }
}
