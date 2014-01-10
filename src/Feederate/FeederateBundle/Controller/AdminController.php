<?php

namespace Feederate\FeederateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Feederate\FeederateBundle\Entity\Invitation;
use Feederate\FeederateBundle\Form\InvitationType;

class AdminController extends Controller
{
    /**
     * Index action
     *
     * @Route("/admin")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Invitation action
     *
     * @Route("/admin/invitations/request")
     * @Template()
     */
    public function invitationRequestAction(Request $request)
    {
        $manager = $this->get('doctrine.orm.entity_manager');
        $entity  = new Invitation();
        $form    = $this->container->get('form.factory')->createNamed('', new InvitationType(), $entity);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                $manager->persist($entity);
                $manager->flush();


            }
        }


        return array('form' => $form->createView());
    }

    /**
     * Invitation action
     *
     * @Route("/admin/invitations")
     * @Template()
     */
    public function invitationsAction()
    {
        $invitations = $this->get('doctrine.orm.entity_manager')
            ->getRepository('FeederateFeederateBundle:Invitation')
            ->findAll();

        return array('invitations' => $invitations);
    }
}
