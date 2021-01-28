<?php
namespace App\DataFixtures;

use App\Entity\Question\Category;
use App\Entity\User\User;
use App\Service\Question\CategoryService;
use App\Service\User\UserService;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Fixture класс для приложения.
 * Они будут собраны в единый класс, что бы соблюсти необходиму последовательность.
 */
class AppFixtures extends BaseFixture
{
    /**
     * @var UserService User Service
     */
    private UserService $userService;

    /**
     * @var CategoryService Question Categoru Service
     */
    private CategoryService $categoryService;

    /**
     * @var UserPasswordEncoderInterface Password Encoder
     */
    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * @var SluggerInterface Slugger
     */
    private SluggerInterface $slugger;

    /**
     * Конструктор класса
     *
     * @param UserService $userService
     * @param CategoryService $categoryService
     * @param UserPasswordEncoderInterface $passwordEncoder Password Encoder
     * @param SluggerInterface $slugger Slugger
     */
    public function __construct(
        UserService $userService,
        CategoryService $categoryService,
        UserPasswordEncoderInterface $passwordEncoder,
        SluggerInterface $slugger
    )
    {
        $this->userService = $userService;
        $this->categoryService = $categoryService;
        $this->passwordEncoder = $passwordEncoder;
        $this->slugger = $slugger;
    }

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager)
    {
        parent::load($manager);

        // загрузка User Fixtures
        $this->loadUserFixtures();

        // загрузка Question Categories Fixtures
        $this->loadQuestionCategoriesFixtures();

        // загрузка Question Fixtures
        $this->loadQuestionFixtures();
    }

    /**
     * Загрузка User Fixtures
     */
    private function loadUserFixtures(): void
    {
        // несколько администраторов
        $staffUsers = ['ivan@webspec.ru', 'ivan@fulledu.ru', 'ivan@hookah.ru'];
        foreach ($staffUsers as $email) {
            $user = new User();
            $user->setUsername(ucfirst(explode('@', $email)[0]));
            $user->setStatus(User::STATUS_ACTIVE);
            $user->setEmail($email);
            $user->setRoles([USER::ROLE_ADMIN]);
            $user->setPlainPassword('1234567890', $this->passwordEncoder);
            $user->setAbout("Всем привет!");

            $this->userService->updateUser($user);
        }

        // и несколько сотен обычных пользователей
        for ($i = 0; $i < 300; $i++) {
            $user = new User();
            $user->setUsername($this->faker->name);
            $user->setStatus(User::STATUS_ACTIVE);
            $user->setEmail($this->faker->email);
            $user->setPlainPassword($this->faker->password(8), $this->passwordEncoder);
            $user->setAbout($this->faker->realText());

            $this->userService->updateUser($user);
        }
    }

    /**
     * Загрузка Question Categories Fixtures
     */
    private function loadQuestionCategoriesFixtures()
    {
        // 20 категорий будет достаточно
        for ($i = 0; $i < 20; $i++) {
            $category = new Category();
            $category->setStatus(Category::STATUS_ACTIVE);
            $category->setTitle($this->faker->name);
            $category->setSlug($this->slugger->slug($category->getTitle()));
            $category->setHref("/category/".$category->getSlug()."/");
            $category->setTotalQuestions(0);

            $this->categoryService->updateCategory($category);
        }
    }

    /**
     * Загрузка Question Fixtures
     */
    private function loadQuestionFixtures()
    {

    }
}
