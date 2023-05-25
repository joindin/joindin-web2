<?php

namespace Middleware;

use Slim\Middleware;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Csrf\TokenStorage\NativeSessionTokenStorage;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

/**
 * Middleware for Slim used to integrate Symfony forms into Slim.
 *
 * This Middleware adds the following services to the slim container:
 *
 * - 'translator', translation class; only added if not already present.
 * - 'formFactory', a factory that creates form instances from form type definitions.
 *
 * For more information on usage, see the {@see self::call()} method.
 */
class FormMiddleware extends Middleware
{
    const DEFAULT_LAYOUT = '_common/joindin_form_div_layout.html.twig';

    const SERVICE_FORM_FACTORY = 'formFactory';

    /** @var string  */
    private $csrfSecret;

    /** @var string */
    private $locale;

    /**
     * Initializes the form middleware with a custom secret and the language used to translate messages.
     *
     * @param string|null $csrfSecret A string used to generate CSRF tokens with; defaults to an md5 hash of
     *     the current folder.
     * @param string      $locale     The locale in country_DIALECT notation, for example: en_UK
     */
    public function __construct($csrfSecret = null, $locale = 'en_UK')
    {
        $this->csrfSecret = $csrfSecret ?: md5(__DIR__);
        $this->locale     = $locale;
    }

    /**
     * Adds a formFactory service and translator, if it does not exist yet, to the Slim container.
     *
     * The form factory can be used to instantiate new forms using Form Type definitions.
     *
     * For example:
     *
     * ```
     *   $factory            = $this->application->formFactory;
     *   $formTypeDefinition = new EventFormType();
     *   $form               = $factory->create($formTypeDefinition);
     * ```
     *
     * @return void
     */
    public function call()
    {
        if (! $this->app->translator instanceof Translator) {
            $this->initializeTranslator();
        }

        $csrfGenerator    = new UriSafeTokenGenerator();
        $csrfStorage      = new NativeSessionTokenStorage();
        $csrfTokenManager = new CsrfTokenManager($csrfGenerator, $csrfStorage);

        $env = $this->getTwigEnvironment();
        $this->addFormTemplatesFolderToLoader($this->getChainingLoader($env));
        $env->addExtension(new TranslationExtension($this->app->translator));
        $this->addFormTwigExtension(self::DEFAULT_LAYOUT, $csrfTokenManager, $env);

        $formMiddleWare = $this;

        $this->app->container->singleton(
            self::SERVICE_FORM_FACTORY,
            function () use ($formMiddleWare, $csrfTokenManager) {
                return $formMiddleWare->createFormFactory($csrfTokenManager);
            }
        );

        $this->next->call();
    }

    /**
     * Method used to create a new Form Factory instance.
     *
     * Generally this method does not need to be called directly; it is used in a callback that created a shared
     * instance in the container of Slim.
     *
     * @param string $csrfSecret
     *
     * @see self::call() where this method is used to construct a shared instance in Slim.
     *
     * @return FormFactoryInterface
     */
    public function createFormFactory($csrfManager)
    {
        $builder = Forms::createFormFactoryBuilder()
            ->addExtension(new CsrfExtension($csrfManager))
            ->setResolvedTypeFactory(new ResolvedFormTypeFactory());

        if ($this->app->validator) {
            $this->addValidatorExtensionToFactoryBuilder($builder);
        }

        return $builder->getFormFactory();
    }

    /**
     * Returns the Twig Loader as a Chained Loader or creates it if necessary.
     *
     * We need to add new loaders in Twig and this can only be done with a chained loader as this
     * allows you to combine multiple together. This method retrieves the loader from Twig and replaces
     * it with a chained version if is not already.
     *
     * @param \Twig_Environment $env
     *
     * @return \Twig_Loader_Chain
     */
    private function getChainingLoader($env)
    {
        $loader = $env->getLoader();
        if (!$loader instanceof \Twig_Loader_Chain) {
            $loader = new \Twig_Loader_Chain([$loader]);
            $env->setLoader($loader);
        }

        return $loader;
    }

    /**
     * Adds a loader to Twig pointing to the location of the default templates for forms.
     *
     * @param \Twig_Loader_Chain $loader
     *
     * @return void
     */
    private function addFormTemplatesFolderToLoader(\Twig_Loader_Chain $loader)
    {
        $reflected = new \ReflectionClass(FormExtension::class);
        $path      = dirname($reflected->getFileName()) . '/../Resources/views/Form';
        $loader->addLoader(new \Twig_Loader_Filesystem($path));
    }

    /**
     * Adds Twig rendering capabilities to the form and use the given template as default basis.
     *
     * @param string           $formLayoutTemplate
     * @param CsrfTokenManager $csrfTokenManager
     * @param Environment      $twig
     */
    private function addFormTwigExtension($formLayoutTemplate, CsrfTokenManager $csrfTokenManager, Environment $twig)
    {
        $formEngine = new TwigRendererEngine([$formLayoutTemplate], $twig);
        $twig->addRuntimeLoader(new FactoryRuntimeLoader([
            FormRenderer::class => function () use ($formEngine, $csrfTokenManager) {
                return new FormRenderer($formEngine, $csrfTokenManager);
            },
        ]));
        $twig->addExtension(new FormExtension());
    }

    /**
     * Initializes a new 'translator' service with array and XLIFF translation capabilities into the Slim Container.
     *
     * @return void
     */
    private function initializeTranslator()
    {
        $this->app->translator = new Translator($this->locale, new MessageFormatter());
        $this->app->translator->addLoader('array', new ArrayLoader());
        $this->app->translator->addLoader('xliff', new XliffFileLoader());
    }

    /**
     * Returns the Twig Environment from the application's view layer.
     *
     * @return \Twig_Environment
     */
    private function getTwigEnvironment()
    {
        return $this->app->view()->getEnvironment();
    }

    /**
     * Adds validation capabilities to the form, including translations for the messages.
     *
     * @param FormFactoryBuilder $builder
     *
     * @return void
     */
    protected function addValidatorExtensionToFactoryBuilder(FormFactoryBuilder $builder)
    {
        $builder->addExtension(new ValidatorExtension($this->app->validator));

        if (isset($this->app->translator)) {
            $r = new \ReflectionClass(Form::class);
            $this->app->translator->addResource(
                'xliff',
                dirname($r->getFilename()) . '/Resources/translations/validators.' . $this->locale . '.xlf',
                $this->locale,
                'validators'
            );
        }
    }
}
