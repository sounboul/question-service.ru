<?php
namespace App\Form\User;

use App\Dto\User\UserRegistrationForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма для регистрации пользователя
 */
class UserRegistrationFormType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Пароль',
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Я ознакомился с правилами сервиса и принимаю их',
            ])
        ;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserRegistrationForm::class,
        ]);
    }
}
