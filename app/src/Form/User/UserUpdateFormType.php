<?php
namespace App\Form\User;

use App\Dto\User\UserUpdateForm;
use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма для редактирования пользователя
 */
class UserUpdateFormType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Имя',
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Роли',
                'choices' => array_flip(User::$roleList),
                'multiple' => true,
                'required' => false,
            ])
            ->add('about', TextareaType::class, [
                'label' => 'О себе',
                'required' => false,
                'attr' => [
                    'rows' => 10,
                ],
            ])
            ->add('photo', FileType::class, [
                'label' => 'Фото',
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
            'data_class' => UserUpdateForm::class,
        ]);
    }
}
