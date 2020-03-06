<?php

namespace Esc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

/**
 * @author Alessandro Bellanda <a.bellanda@gmail.com>
 */
class RequestParams
{
    public static function fromRequest(Request $request): AttributeBag
    {
        $requestParams = new AttributeBag();
        $filters = $request->get('filters', '{}');
        if (!is_string($filters)) {
            throw new \RuntimeException('Filters must be a string');
        }
        $requestParams->set('filters', json_decode($filters, true, 512, JSON_THROW_ON_ERROR) ?? []);

        $sortBy = $request->get('sortBy');
        $requestParams->set(
            'sortBy',
            $sortBy === null ? [] : [$sortBy => $request->get('descending') ? 'DESC' : 'ASC']
        );

        $rowsPerPage = (int)$request->get('rowsPerPage');
        $requestParams->set('limit', $rowsPerPage > 0 ? $rowsPerPage : null);

        $page = (int)$request->get('page');
        $requestParams->set('offset', ($page > 0 ? $page - 1 : 0) * ($rowsPerPage ?: 0));

        return $requestParams;
    }
}
