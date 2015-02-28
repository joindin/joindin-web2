<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests\Extension\Core\Type;

use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\Intl\Util\IntlTestHelper;

class LanguageTypeTest extends TypeTestCase
{
    protected function setUp()
    {
        IntlTestHelper::requireIntl($this);

        parent::setUp();
    }

    public function testCountriesAreSelectable()
    {
        $form = $this->factory->create('language');
        $view = $form->createView();
        $choices = $view->vars['choices'];

        $this->assertContains(new ChoiceView('en', 'en', 'English'), $choices, '', false, false);
        $this->assertContains(new ChoiceView('en_GB', 'en_GB', 'British English'), $choices, '', false, false);
        $this->assertContains(new ChoiceView('en_US', 'en_US', 'American English'), $choices, '', false, false);
        $this->assertContains(new ChoiceView('fr', 'fr', 'French'), $choices, '', false, false);
        $this->assertContains(new ChoiceView('my', 'my', 'Burmese'), $choices, '', false, false);
    }

    public function testMultipleLanguagesIsNotIncluded()
    {
        $form = $this->factory->create('language', 'language');
        $view = $form->createView();
        $choices = $view->vars['choices'];

        $this->assertNotContains(new ChoiceView('mul', 'mul', 'Mehrsprachig'), $choices, '', false, false);
    }
}
