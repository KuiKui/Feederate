<?php

namespace Feederate\FeederateBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Feederate\FeederateBundle\Form\UserType;

/**
 * Class UserController
 *
 * @package Feederate\FeederateBundle\Controller
 */
class UserController extends FOSRestController
{
    /**
     * Get current user
     *
     * @return \FOS\RestBundle\View\View
     *
     */
    public function getUserAction()
    {
        return $this->view($this->getUser(), 200);
    }

    /**
     * Update current user
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Rest\Route(
     *     pattern="/user"
     * )
     */
    public function postUserAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->container->get('form.factory')->createNamed('', new UserType(), $user);

        $form->submit($request);

        if ($form->isValid()) {

            $this->get('doctrine.orm.entity_manager')->flush();

            return $this->view('', 204);
        }

        return $this->view($form, 422);
    }
}
