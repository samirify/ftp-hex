<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Api\Controller;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseController
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        protected ContainerInterface $container
    ) {
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getRequestParams(Request $request): array
    {
        return array_merge(
            $request->request->all(),
            $request->query->all(),
            json_decode($request->getContent(), true) ?? []
        );
    }
}
