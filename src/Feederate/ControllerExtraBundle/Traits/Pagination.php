<?php

namespace Feederate\ControllerExtraBundle\Traits;

use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * Useful methods to paginate
 *
 * @author Florent Dubost <florent.dubost@gmail.com>
 */
trait Pagination {
    /**
     * Get start and limit from page and perPage
     *
     * @param int $page       Page number
     * @param int $perPage    Items per page
     * @param int $maxPerPage Max items per page
     *
     * @return array(start, limit)
     */
    public function getStartAndLimit($page, $perPage = 10, $maxPerPage = 100)
    {
        $page    = max($page, 1);
        $perPage = max(min($perPage, $maxPerPage), 1);
        $start   = ($page - 1) * $perPage;

        return array($start, $perPage);
    }

    /**
     * Get start and limit from ParamFetcher
     *
     * @param ParamFetcher $paramFetcher ParamFetcher
     *
     * @return array(start, limit)
     */
    public function getStartAndLimitFromParams(ParamFetcherInterface $paramFetcher)
    {
        return $this->getStartAndLimit($paramFetcher->get('page'), $paramFetcher->get('per_page'));
    }
}