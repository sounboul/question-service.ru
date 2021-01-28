<?php
namespace App\DataFixtures;

use App\Entity\User\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Fixture класс для User Entity
 */
class UserFixtures extends BaseFixture
{
    /**
     * @var UserPasswordEncoderInterface Password Encoder
     */
    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * Конструктор класса
     *
     * @param UserPasswordEncoderInterface $passwordEncoder Password Encoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager)
    {
        parent::load($manager);

        // несколько администраторов
        $staffUsers = ['ivan@webspec.ru', 'ivan@fulledu.ru', 'ivan@hookah.ru'];
        foreach ($staffUsers as $email) {
            $this->createOne(User::class, function (User $user) use ($email) {
                $user->setUsername(ucfirst(explode('@', $email)[0]));
                $user->setStatus(User::STATUS_ACTIVE);
                $user->setEmail($email);
                $user->setRoles([USER::ROLE_ADMIN]);
                $user->setPlainPassword('1234567890', $this->passwordEncoder);
                $user->setAbout("Всем привет!");

                return $user;
            });
        }

        // и несколько сотен обычных пользователей
        $this->createMany(User::class, 300, function (User $user) {
            $user->setUsername($this->faker->name);
            $user->setStatus(User::STATUS_ACTIVE);
            $user->setEmail($this->faker->email);
            $user->setPlainPassword($this->faker->password(8), $this->passwordEncoder);
            $user->setAbout($this->faker->realText());

            return $user;
        });
    }
}
