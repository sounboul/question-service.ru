<?php
namespace App\DataFixtures;

use App\Dto\Question\CategoryForm;
use App\Dto\User\ProfileForm;
use App\Dto\User\RegistrationForm;
use App\Dto\User\UserForm;
use App\Entity\Question\Answer;
use App\Entity\Question\Category;
use App\Entity\Question\Question;
use App\Entity\User\User;
use App\Service\Question\CategoryService;
use App\Service\Question\QuestionService;
use App\Service\Question\AnswerService;
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
     * @var CategoryService Category Service
     */
    private CategoryService $categoryService;

    /**
     * @var QuestionService Question Service
     */
    private QuestionService $questionService;

    /**
     * @var AnswerService Answer Service
     */
    private AnswerService $answerService;

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
     * @param CategoryService $categoryService
     * @param AnswerService $answerService
     * @param UserPasswordEncoderInterface $passwordEncoder Password Encoder
     * @param SluggerInterface $slugger Slugger
     */
    public function __construct(
        UserService $userService,
        QuestionService $questionService,
        CategoryService $categoryService,
        AnswerService $answerService,
        UserPasswordEncoderInterface $passwordEncoder,
        SluggerInterface $slugger
    )
    {
        $this->userService = $userService;
        $this->questionService = $questionService;
        $this->categoryService = $categoryService;
        $this->answerService = $answerService;
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
            // регистрация
            $formData = new RegistrationForm();
            $formData->email = $email;
            $formData->password = '1234567890';
            $formData->agreeTerms = true;

            $user = $this->userService->create($formData, false);

            // заполнение профиля
            $formData = new UserForm();
            $formData->email = $user->getEmail();
            $formData->username = $user->getUsername();
            $formData->about = 'Всем привет!';
            $formData->roles = ["ROLE_ADMIN"];

            $this->users[] = $this->userService->updateUser($user->getId(), $formData);
        }

        // и несколько сотен обычных пользователей
        for ($i = 0; $i < 300; $i++) {
            // регистрация
            $formData = new RegistrationForm();
            $formData->email = $this->faker->email;
            $formData->password = $this->faker->password(8);
            $formData->agreeTerms = true;

            $user = $this->userService->create($formData, false);

            // заполнение профиля
            $formData = new ProfileForm();
            $formData->username = $this->faker->name;
            $formData->about = $this->faker->realText();

            $this->users[] = $this->userService->updateProfile($user->getId(), $formData);
        }
    }

    /**
     * Загрузка Question Categories Fixtures
     *
     * @throws \App\Exception\EntityValidationException
     */
    private function loadQuestionCategoriesFixtures()
    {
        // 20 категорий будет достаточно
        for ($i = 0; $i < 20; $i++) {
            $category = new CategoryForm();
            $category->title = $this->faker->name;
            $category->slug = $this->slugger->slug($category->title);

            $this->categories[] = $this->categoryService->create($category);
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

                $this->answerService->updateAnswer($answer);
            }
        }
    }
}
