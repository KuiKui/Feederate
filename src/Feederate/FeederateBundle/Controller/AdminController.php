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
     * @Route("/admin/invitations")
     * @Template()
     */
    public function invitationsAction(Request $request)
    {
        $manager = $this->get('doctrine.orm.entity_manager');

        $invitations = $manager
            ->getRepository('FeederateFeederateBundle:Invitation')
            ->findAll();

        $invitation = new Invitation();
        $form       = $this->container->get('form.factory')->createNamed('', new InvitationType(), $invitation);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                $manager->persist($invitation);
                $manager->flush();

                return $this->redirect($this->generateUrl('feederate_feederate_admin_invitations'));
            }
        }

        return [
            'invitations' => $invitations,
            'form'        => $form->createView(),
        ];
    }

    /**
     * Invitation action
     *
     * @Route("/admin/invitations/{email}/sent")
     * @Template()
     */
    public function invitationsSentAction(Request $request, $email)
    {
        $manager = $this->get('doctrine.orm.entity_manager');

        $invitation = $manager
            ->getRepository('FeederateFeederateBundle:Invitation')
            ->findOneBy(['email' => $email]);

        if (!$invitation) {
            throw new \Exception(sprintf("Invitation with id %d does not exists.", $id));
        }

        $invitation->send();
        $manager->persist($invitation);
        $manager->flush();

        return $this->redirect($this->generateUrl('feederate_feederate_admin_invitations'));
    }

    /**
     * Users stats action
     *
     * @Route("/admin/users-stats")
     * @Template()
     */
    public function usersStatsAction(Request $request)
    {
        $manager = $this->get('doctrine.orm.entity_manager');

        $users = $manager
            ->getRepository('FeederateFeederateBundle:User')
            ->findBy(array());

        foreach($users as $user) {
            $user->entries = 0;
            $user->unread  = 0;
            foreach($user->getUserFeeds() as $userFeed) {
                $user->entries += count($userFeed->getFeed()->getEntries());
                $user->unread  += $userFeed->getUnreadCount();
            }
        }

        return ['users' => $users];
    }
}
