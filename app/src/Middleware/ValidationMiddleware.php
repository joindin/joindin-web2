<?php

namespace Middleware;

use Slim\Middleware;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * In this middleware we create the validation services as provided by Symfony and register it as service in the
 * container of Slim. If this middleware is registered before the FormMiddleware then the validator service is also
 * added to the Symfony Forms Component; and adding validation capability to the forms.
 *
 * This Middleware adds the following services to the slim container:
 *
 * - 'translator', translation class; only added if not already present.
 * - 'validator', the validation service that can be used to verify data against a set of constraints.
 *
 * For more information on usage, see the {@see self::call()} method.
 */
class ValidationMiddleware extends Middleware
{
    const SERVICE_VALIDATOR = 'validator';

    /** @var string */
    private $locale;

    /**
     * Initializes this middleware with the given locale.
     *
     * @param string $locale The locale in country_DIALECT notation, for example: en_UK
     */
    public function __construct($locale = 'en_UK')
    {
        $this->locale = $locale;
    }

    /**
     * Adds a new 'validator' service to the Slim container.
     *
     * This validator must only be called if you are not using the forms functionality; when using forms you
     * can use the 'constraints' option of a Field Definition to add validation. See the Symfony documentation
     * for more information.
     *
     * @return void
     */
    public function call()
    {
        $this->addTranslations($this->getTranslator(), $this->getTranslationsRootFolder());

        $middleware = $this;
        $this->app->container->singleton(
            self::SERVICE_VALIDATOR,
            function () use ($middleware) {
                return $middleware->createValidator();
            }
        );

        $this->next->call();
    }

    /**
     * Returns a new validator instance.
     *
     * Generally this method does not need to be called directly; it is used in a callback that created a shared
     * instance in the container of Slim.
     *
     * @see self::call() where this method is used to construct a shared instance in Slim.
     *
     * @return Validator\ValidatorInterface
     */
    public function createValidator()
    {
        $validator = Validation::createValidatorBuilder()
            ->setMetadataFactory(new LazyLoadingMetadataFactory(new StaticMethodLoader()))
            ->setConstraintValidatorFactory(new ConstraintValidatorFactory())
            ->setTranslator($this->getTranslator())
            ->getValidator();

        return $validator;
    }

    /**
     * Adds all messages related to validation to the translator.
     *
     * @param Translator $translator
     * @param string     $validatorComponentRootFolder
     *
     * @return void
     */
    private function addTranslations($translator, $validatorComponentRootFolder)
    {
        $translator->addResource(
            'xliff',
            $validatorComponentRootFolder . '/Resources/translations/validators.' . $this->locale . '.xlf',
            $this->locale,
            'validators'
        );
    }

    /**
     * Retrieves the translator from the container and creates it if it is absent.
     *
     * @return TranslatorInterface
     */
    private function getTranslator()
    {
        if (!$this->app->translator instanceof TranslatorInterface) {
            $this->app->translator = new Translator($this->locale);
            $this->app->translator->addLoader('array', new ArrayLoader());
            $this->app->translator->addLoader('xliff', new XliffFileLoader());
        }

        return $this->app->translator;
    }

    /**
     * Returns the folder where the translations for the validator are stored.
     *
     * @return string
     */
    private function getTranslationsRootFolder()
    {
        $r = new \ReflectionClass('Symfony\Component\Validator\Validation');

        return dirname($r->getFilename());
    }
}
