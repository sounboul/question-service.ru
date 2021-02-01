<?php
namespace App\Form\Question;

use App\Dto\Question\QuestionCreateForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Service\Question\CategoryService;

/**
 * Форма создания вопроса
 */
class QuestionCreateFormType extends AbstractType
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
            ->add('categoryId', ChoiceType::class, [
                'label' => 'Категория',
                'choices' => array_flip($categoryService->getListForDropdown()),
            ])
            ->add('title', TextType::class, [
                'label' => 'Вопрос',
            ])
            ->add('text', TextareaType::class, [
                'label' => 'Текст вопроса',
                'required' => false,
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
            'data_class' => QuestionCreateForm::class,
        ]);

        $resolver->setRequired('categoryService');
    }
}
