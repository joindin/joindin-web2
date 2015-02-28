<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form;

use Symfony\Component\Form\Exception\ExceptionInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\InvalidArgumentException;

/**
 * The central registry of the Form component.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FormRegistry implements FormRegistryInterface
{
    /**
     * Extensions
     *
     * @var FormExtensionInterface[] An array of FormExtensionInterface
     */
    private $extensions = array();

    /**
     * @var FormTypeInterface[]
     */
    private $types = array();

    /**
     * @var FormTypeGuesserInterface|false|null
     */
    private $guesser = false;

    /**
     * @var ResolvedFormTypeFactoryInterface
     */
    private $resolvedTypeFactory;

    /**
     * Constructor.
     *
     * @param FormExtensionInterface[]         $extensions          An array of FormExtensionInterface
     * @param ResolvedFormTypeFactoryInterface $resolvedTypeFactory The factory for resolved form types.
     *
     * @throws UnexpectedTypeException if any extension does not implement FormExtensionInterface
     */
    public function __construct(array $extensions, ResolvedFormTypeFactoryInterface $resolvedTypeFactory)
    {
        foreach ($extensions as $extension) {
            if (!$extension instanceof FormExtensionInterface) {
                throw new UnexpectedTypeException($extension, 'Symfony\Component\Form\FormExtensionInterface');
            }
        }

        $this->extensions = $extensions;
        $this->resolvedTypeFactory = $resolvedTypeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (!is_string($name)) {
            throw new UnexpectedTypeException($name, 'string');
        }

        if (!isset($this->types[$name])) {
            $type = null;

            foreach ($this->extensions as $extension) {
                if ($extension->hasType($name)) {
                    $type = $extension->getType($name);
                    break;
                }
            }

            if (!$type) {
                throw new InvalidArgumentException(sprintf('Could not load type "%s"', $name));
            }

            $this->resolveAndAddType($type);
        }

        return $this->types[$name];
    }

    /**
     * Wraps a type into a ResolvedFormTypeInterface implementation and connects
     * it with its parent type.
     *
     * @param FormTypeInterface $type The type to resolve.
     *
     * @return ResolvedFormTypeInterface The resolved type.
     */
    private function resolveAndAddType(FormTypeInterface $type)
    {
        $parentType = $type->getParent();

        if ($parentType instanceof FormTypeInterface) {
            $this->resolveAndAddType($parentType);
            $parentType = $parentType->getName();
        }

        $typeExtensions = array();

        foreach ($this->extensions as $extension) {
            $typeExtensions = array_merge(
                $typeExtensions,
                $extension->getTypeExtensions($type->getName())
            );
        }

        $this->types[$type->getName()] = $this->resolvedTypeFactory->createResolvedType(
            $type,
            $typeExtensions,
            $parentType ? $this->getType($parentType) : null
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name)
    {
        if (isset($this->types[$name])) {
            return true;
        }

        try {
            $this->getType($name);
        } catch (ExceptionInterface $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeGuesser()
    {
        if (false === $this->guesser) {
            $guessers = array();

            foreach ($this->extensions as $extension) {
                $guesser = $extension->getTypeGuesser();

                if ($guesser) {
                    $guessers[] = $guesser;
                }
            }

            $this->guesser = !empty($guessers) ? new FormTypeGuesserChain($guessers) : null;
        }

        return $this->guesser;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
}
