<?php

namespace Feederate\FeederateBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Form\FeedType;

/**
 * Class FeedController
 *
 * @package Feederate\FeederateBundle\Controller
 */
class FeedController extends FOSRestController implements ClassResourceInterface
{

    /**
     * Feed list
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction()
    {
        $repository = $this->get('doctrine.orm.entity_manager')->getRepository('FeederateFeederateBundle:Feed');
        $entities   = $repository->findBy([], ['id' => 'DESC']);

        return $this->view($entities, 200);
    }

    /**
     * Feed list
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Rest\Route(requirements={"id"="\d+"})
     */
    public function getAction($id)
    {
        $repository = $this->get('doctrine.orm.entity_manager')->getRepository('FeederateFeederateBundle:Feed');
        $entity     = $repository->find($id);

        if (!$entity) {
            return $this->view(sprintf('Feed with id %s not found', $id), 404);
        }

        return $this->view($entity, 200);
    }

    /**
     * Feed post
     *
     * @return \FOS\RestBundle\View\View
     */
    public function postAction(Request $request)
    {
        $manager = $this->get('doctrine.orm.entity_manager');
        $entity  = new Feed();
        $form    = $this->container->get('form.factory')->createNamed('', new FeedType(), $entity);

        $form->submit($request);

        if ($form->isValid()) {
            $manager->persist($entity);
            $manager->flush();

            return $this->view($entity, 201, array(
                'Location' => $this->generateUrl('get_feed', ['id' => $entity->getId()], true),
            ));
        }

        return $this->view($form, 422);
    }
}
