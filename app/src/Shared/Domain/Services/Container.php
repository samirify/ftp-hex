<?php
declare(strict_types=1);

namespace App\Shared\Domain\Services;

use App\Shared\Domain\Exception\ContainerException;
use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Class Container
 */
class Container implements ContainerInterface
{
    /** @var array  */
    private array $dependencies = [];

    /**
     * @param string $id
     *
     * @return mixed|object|string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function get(string $id): mixed
    {
        if ($this->has($id)) {
            $entry = $this->dependencies[$id];

            if (is_callable($entry)) {
                return $entry($this);
            }

            $id = $entry;
        }

        return $this->resolve($id);
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->dependencies[$id]);
    }

    /**
     * @param string $id
     * @param Closure|callable|string $class
     *
     * @return void
     */
    public function set(string $id, Closure|callable|string $class): void
    {
        $this->dependencies[$id] = $class;
    }

    /**
     * @param string $id
     * @return mixed|object|string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function resolve(string $id): mixed
    {
        $reflectionClass = new ReflectionClass($id);

        if (!$reflectionClass->isInstantiable()) {
            throw new ContainerException('Class "' . $id . '" is not instantiable');
        }

        $constructor = $reflectionClass->getConstructor();

        if (! $constructor) {
            return new $id;
        }

        $parameters = $constructor->getParameters();

        if (! $parameters) {
            return new $id;
        }

        $dependencies = array_map(
            function (\ReflectionParameter $param) use ($id) {
                $name = $param->getName();
                $type = $param->getType();

                if (!$type) {
                    throw new ContainerException(
                        'Failed to resolve class "' . $id . '" because param "' . $name . '" is missing a type hint'
                    );
                }

                if ($type instanceof \ReflectionUnionType) {
                    throw new ContainerException(
                        'Failed to resolve class "' . $id . '" because of union type for param "' . $name . '"'
                    );
                }

                if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
                    return $this->get($type->getName());
                }

                throw new ContainerException(
                    'Failed to resolve class "' . $id . '" because invalid param "' . $name . '"'
                );
            },
            $parameters
        );

        return $reflectionClass->newInstanceArgs($dependencies);
    }
}