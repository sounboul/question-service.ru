<?php
namespace App\Form;

use App\Form\EventListener\ReCaptchaValidationListener;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Поле формы типа ReCaptcha
 */
class ReCaptchaType extends AbstractType
{
    /**
     * @var ReCaptcha ReCaptcha
     */
    private ReCaptcha $reCaptcha;

    /**
     * ReCaptchaType constructor.
     *
     * @param ReCaptcha $reCaptcha
     */
    public function __construct(ReCaptcha $reCaptcha)
    {
        $this->reCaptcha = $reCaptcha;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new ReCaptchaValidationListener($this->reCaptcha));
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form,   array $options)
    {
        $view->vars['type'] = $options['type'];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'error_bubbling' => false,
            'label' => false,
            'type' => 'invisible',
        ]);

        $resolver->setAllowedValues('type', ['checkbox', 'invisible']);
    }
}
