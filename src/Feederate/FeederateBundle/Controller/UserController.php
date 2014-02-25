<?php

namespace Feederate\FeederateBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;

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
}
