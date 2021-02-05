<?php
namespace App\Form\Question;

use App\Dto\Question\AnswerCreateForm;
use App\Form\ReCaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма создания ответа
 */
class AnswerCreateFormType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', TextareaType::class, [
                'label' => 'Текст ответа',
                'attr' => [
                    'rows' => 10,
                ],
            ])
        ;

        // Recaptcha опциональна для формы
        if ($options['recaptcha']) {
            $builder->add('recaptcha', ReCaptchaType::class, [
                'mapped' => false,
                'type' => 'checkbox',
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AnswerCreateForm::class,
            'recaptcha' => false,
        ]);

        $resolver->setAllowedValues('recaptcha', [true, false]);
    }
}
