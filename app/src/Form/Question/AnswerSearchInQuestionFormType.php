<?php
namespace App\Form\Question;

use App\Dto\Question\AnswerSearchForm;
use App\Entity\Question\Answer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма поиска по ответам в конкретном вопросе
 */
class AnswerSearchInQuestionFormType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('status', ChoiceType::class, [
                'label' => 'Статус',
                'choices' => array_flip(Answer::$statusList),
                'empty_data' => Answer::STATUS_ACTIVE,
            ])
            ->add('text', TextType::class, [
                'label' => 'Текст',
                'required' => false,
            ])
            ->add('userId', TextType::class, [
                'label' => 'Автор',
                'required' => false,
            ])
            ->add('createdByIp', TextType::class, [
                'label' => 'IP автора',
                'required' => false,
            ])
        ;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AnswerSearchForm::class,

            // enable/disable CSRF protection for this form
            'csrf_protection' => false,
        ]);
    }
}
