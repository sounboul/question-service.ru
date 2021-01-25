<?php
namespace App\Form\User;

use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Форма для редактирования профиля пользователя
 */
class ProfileFormType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Имя',
                'constraints' => [
                    new NotBlank([
                        'message' => "Необходимо заполнить поле 'Имя'",
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Имя должно содержать минимум {{ limit }} символа',
                        'max' => 100,
                        'maxMessage' => 'Имя должно содержать максимум {{ limit }} символов',
                    ]),
                ],
            ])
            ->add('photo', FileType::class, [
                'label' => 'Фото',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '5M',

                        'minWidth' => 400,
                        'minHeight' => 400,

                        'maxWidth' => 3000,
                        'maxHeight' => 3000,
                    ]),
                ],
            ])
            ->add('about', TextareaType::class, [
                'label' => 'О себе',
                'required' => false,
                'attr' => [
                    'rows' => 10,
                ],
                'constraints' => [
                    new Length([
                        'max' => 3000,
                        'maxMessage' => 'О себе должно содержать максимум {{ limit }} символов',
                    ]),
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
            'data_class' => User::class,
        ]);
    }
}
