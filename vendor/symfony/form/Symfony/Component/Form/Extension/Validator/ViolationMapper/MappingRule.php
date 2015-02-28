<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Validator\ViolationMapper;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Exception\ErrorMappingException;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class MappingRule
{
    /**
     * @var FormInterface
     */
    private $origin;

    /**
     * @var string
     */
    private $propertyPath;

    /**
     * @var string
     */
    private $targetPath;

    public function __construct(FormInterface $origin, $propertyPath, $targetPath)
    {
        $this->origin = $origin;
        $this->propertyPath = $propertyPath;
        $this->targetPath = $targetPath;
    }

    /**
     * @return FormInterface
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Matches a property path against the rule path.
     *
     * If the rule matches, the form mapped by the rule is returned.
     * Otherwise this method returns false.
     *
     * @param string $propertyPath The property path to match against the rule.
     *
     * @return null|FormInterface The mapped form or null.
     */
    public function match($propertyPath)
    {
        if ($propertyPath === (string) $this->propertyPath) {
            return $this->getTarget();
        }
    }

    /**
     * Matches a property path against a prefix of the rule path.
     *
     * @param string $propertyPath The property path to match against the rule.
     *
     * @return bool Whether the property path is a prefix of the rule or not.
     */
    public function isPrefix($propertyPath)
    {
        $length = strlen($propertyPath);
        $prefix = substr($this->propertyPath, 0, $length);
        $next = isset($this->propertyPath[$length]) ? $this->propertyPath[$length] : null;

        return $prefix === $propertyPath && ('[' === $next || '.' === $next);
    }

    /**
     * @return FormInterface
     *
     * @throws ErrorMappingException
     */
    public function getTarget()
    {
        $childNames = explode('.', $this->targetPath);
        $target = $this->origin;

        foreach ($childNames as $childName) {
            if (!$target->has($childName)) {
                throw new ErrorMappingException(sprintf('The child "%s" of "%s" mapped by the rule "%s" in "%s" does not exist.', $childName, $target->getName(), $this->targetPath, $this->origin->getName()));
            }
            $target = $target->get($childName);
        }

        return $target;
    }
}
