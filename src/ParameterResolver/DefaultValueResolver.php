<?php

namespace Invoker\ParameterResolver;

use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionParameter;

/**
 * Finds the default value for a parameter, *if it exists*.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class DefaultValueResolver implements ParameterResolver
{
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ) {
        $parameters = $reflection->getParameters();

        // Skip parameters already resolved
        if (! empty($resolvedParameters)) {
            $parameters = array_diff_key($parameters, $resolvedParameters);
        }

        foreach ($parameters as $index => $parameter) {
            /** @var \ReflectionParameter $parameter */
            if ($parameter->isOptional()) {
                try {
                    $resolvedParameters[$index] = (($class = $parameter->getClass()) === null)
                        ? $parameter->getDefaultValue()
                        : $this->getObjectInstance($parameter, $this->getContainer($providedParameters));
                } catch (ReflectionException $e) {
                    // Can't get default values from PHP internal classes and functions
                }
            }
        }

        return $resolvedParameters;
    }

    /**
     * @param ReflectionParameter $parameter
     * @param ContainerInterface|null $container
     * @return mixed|string
     * @throws ReflectionException
     */
    private function getObjectInstance(ReflectionParameter $parameter, ?ContainerInterface $container) {
        return $container->get($parameter->getClass()->getName()) ?? $parameter->getDefaultValue();
    }

    private function getContainer(array $providedParameters): ?ContainerInterface {
        foreach($providedParameters as $p) {
            if ($p instanceof ContainerInterface) {
                return $p;
            }
        }
        return null;
    }
}
