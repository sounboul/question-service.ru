<?php
namespace App\Form\User;

use App\Dto\User\UserUpdateProfileForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма для редактирования профиля пользователя
 */
class UserUpdateProfileFormType extends AbstractType
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
            ->add('photo', FileType::class, [
                'label' => 'Фото',
                'required' => false,
            ])
            ->add('about', TextareaType::class, [
                'label' => 'О себе',
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
            'data_class' => UserUpdateProfileForm::class,
        ]);
    }
}
