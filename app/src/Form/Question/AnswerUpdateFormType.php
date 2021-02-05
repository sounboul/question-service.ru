<?php
namespace App\Form\Question;

use App\Dto\Question\AnswerUpdateForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма редактирования ответа
 */
class AnswerUpdateFormType extends AbstractType
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
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AnswerUpdateForm::class,
        ]);
    }
}
