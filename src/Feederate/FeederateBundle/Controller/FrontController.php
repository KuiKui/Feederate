<?php

namespace Feederate\FeederateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Feederate\FeederateBundle\Form\FeedbackType;

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

        if ($securityContext->isGranted('ROLE_USER')) {
            return $this->appAction();
        } else if ($securityContext->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')) {
            return $this->homeAction();
        }
    }

    /**
     * Feedback action
     *
     * @Route("/feedback")
     */
    public function feedbackAction()
    {
        $form = $this->createForm(new FeedBackType());

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                
                $message = \Swift_Message::newInstance()
                    ->setSubject('[Feedback]')
                    ->setFrom($this->container->getParameter('feedback_email'))
                    ->setTo($this->container->getParameter('feedback_email'))
                    ->setBody($form->getData()['message'])
                    ->setReplyTo($this->getUser()->getEmail());

                $this->get('mailer')->send($message);

                return $this->redirect($this->generateUrl('feederate_feederate_front_feedbackthanks'));
            }
        }

        return $this->render('FeederateFeederateBundle:Front:feedback.html.twig', array(
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Feedback action
     *
     * @Route("/feedback_thanks")
     */
    public function feedbackThanksAction()
    {
        return $this->render('FeederateFeederateBundle:Front:feedbackThanks.html.twig');
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
