<?php
namespace App\Form\User;

use App\Dto\User\UserSearchForm;
use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма поиска пользователей
 */
class UserSearchFormType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('orderBy', ChoiceType::class, [
                'label' => 'Сортировка',
                'choices' => array_flip(UserSearchForm::getAvailableOrderBy()),
                'empty_data' => 'u.id_DESC',
            ])
            ->add('id', NumberType::class, [
                'label' => 'ID',
                'required' => false,
            ])
            ->add('username', TextType::class, [
                'label' => 'Имя пользователя',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Статус',
                'choices' => array_flip(User::$statusList),
                'required' => false,
            ])
            ->add('email', TextType::class, [
                'label' => 'E-mail',
                'required' => false,
            ])
            ->add('emailVerified', ChoiceType::class, [
                'label' => 'E-mail подтвержден',
                'choices' => [
                    'Да' => 1,
                    'Нет' => 0,
                ],
                'required' => false,
            ])
            ->add('emailSubscribed', ChoiceType::class, [
                'label' => 'E-mail подписан на рассылку',
                'choices' => [
                    'Да' => 1,
                    'Нет' => 0,
                ],
                'required' => false,
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Роль',
                'choices' => array_flip(User::$roleList),
                'required' => false,
            ])
            ->add('withPhoto', ChoiceType::class, [
                'label' => 'С фотографией',
                'choices' => [
                    'Да' => 1,
                    'Нет' => 0,
                ],
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
            'data_class' => UserSearchForm::class,

            // enable/disable CSRF protection for this form
            'csrf_protection' => false,
        ]);
    }
}
