<?php
namespace App\Form\Question;

use App\Dto\Question\QuestionSearchForm;
use App\Entity\Question\Question;
use App\Service\Question\CategoryService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма поиска по вопросам
 */
class QuestionSearchFormType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var CategoryService $categoryService
         */
        $categoryService = $options['categoryService'];

        $builder
            ->setMethod('GET')
            ->add('orderBy', ChoiceType::class, [
                'label' => 'Сортировка',
                'choices' => array_flip(QuestionSearchForm::getAvailableOrderBy()),
                'empty_data' => 'q.id_DESC',
            ])
            ->add('id', NumberType::class, [
                'label' => 'ID',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Статус',
                'choices' => array_flip(Question::$statusList),
                'required' => false,
            ])
            ->add('text', TextType::class, [
                'label' => 'Текст',
                'required' => false,
            ])
            ->add('categoryId', ChoiceType::class, [
                'label' => 'Категория',
                'choices' => array_flip($categoryService->getListForDropdown()),
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
            ->add('withoutAnswers', CheckboxType::class, [
                'label' => 'Вопросы без ответа',
                'false_values' => [null, '0'],
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
            'data_class' => QuestionSearchForm::class,

            // enable/disable CSRF protection for this form
            'csrf_protection' => false,
        ]);

        $resolver->setRequired('categoryService');
    }
}
