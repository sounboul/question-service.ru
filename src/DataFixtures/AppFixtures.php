<?php
namespace App\DataFixtures;

use App\Entity\Question\Answer;
use App\Entity\Question\Category;
use App\Entity\Question\Question;
use App\Entity\User\User;
use App\Service\Question\QuestionService;
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
     * @var QuestionService Question Service
     */
    private QuestionService $questionService;

    /**
     * @var UserPasswordEncoderInterface Password Encoder
     */
    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * @var SluggerInterface Slugger
     */
    private SluggerInterface $slugger;

    /**
     * @var array Список пользователей
     */
    private array $users = [];

    /**
     * @var array Список категорий
     */
    private array $categories = [];

    /**
     * Конструктор класса
     *
     * @param UserService $userService
     * @param QuestionService $questionService
     * @param UserPasswordEncoderInterface $passwordEncoder Password Encoder
     * @param SluggerInterface $slugger Slugger
     */
    public function __construct(
        UserService $userService,
        QuestionService $questionService,
        UserPasswordEncoderInterface $passwordEncoder,
        SluggerInterface $slugger
    )
    {
        $this->userService = $userService;
        $this->questionService = $questionService;
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

            $this->users[] = $this->userService->updateUser($user);
        }

        // и несколько сотен обычных пользователей
        for ($i = 0; $i < 300; $i++) {
            $user = new User();
            $user->setUsername($this->faker->name);
            $user->setStatus(User::STATUS_ACTIVE);
            $user->setEmail($this->faker->email);
            $user->setPlainPassword($this->faker->password(8), $this->passwordEncoder);
            $user->setAbout($this->faker->realText());

            $this->users[] = $this->userService->updateUser($user);
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
            $category->setTotalQuestions(0);

            $this->categories[] = $this->questionService->updateCategory($category);
        }
    }

    /**
     * Загрузка Question Fixtures
     */
    private function loadQuestionFixtures()
    {
        for ($i = 0; $i < 500; $i++) {
            // создание вопросы
            $question = new Question();
            $question->setStatus(Question::STATUS_ACTIVE);
            $question->setUser($this->users[array_rand($this->users)]);
            $question->setCategory($this->categories[array_rand($this->categories)]);
            $question->setTitle($this->faker->text(100));
            $question->setText($i % 2 == 0 ? $this->faker->paragraph() : '');
            $question->setSlug($this->slugger->slug($question->getTitle()));
            $question->setHref('');
            $question->setCreatedByIp($this->faker->ipv4);

            $this->questionService->updateQuestion($question);

            // создание ответов к вопросу
            $count = rand(0, 20);
            for ($n = 0; $n < $count; $n++) {
                $answer = new Answer();
                $answer->setStatus(Answer::STATUS_ACTIVE);
                $answer->setUser($this->users[array_rand($this->users)]);
                $answer->setQuestion($question);
                $answer->setText($this->faker->paragraph);
                $answer->setCreatedByIp($this->faker->ipv4);

                $this->questionService->updateAnswer($answer);
            }
        }
    }
}
