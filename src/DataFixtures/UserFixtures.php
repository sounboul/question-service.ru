<?php
namespace App\DataFixtures;

use App\Entity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Fixture класс для User Entity
 */
class UserFixtures extends Fixture
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
        $user = new User();
        $user->setEmail('ivan@webspec.ru');
        $user->setRoles([]);
        $user->setPassword($this->passwordEncoder->encodePassword($user, '123456'));

        $manager->persist($user);
        $manager->flush();
    }
}
