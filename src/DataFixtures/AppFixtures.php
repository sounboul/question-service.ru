<?php
namespace App\DataFixtures;

use App\Dto\Question\AnswerCreateForm;
use App\Dto\Question\CategoryCreateForm;
use App\Dto\Question\QuestionCreateForm;
use App\Dto\User\ProfileForm;
use App\Dto\User\RegistrationForm;
use App\Dto\User\UserForm;
use App\Service\Question\CategoryService;
use App\Service\Question\QuestionService;
use App\Service\Question\AnswerService;
use App\Service\User\UserService;
use Doctrine\Persistence\ObjectManager;
use App\Exception\AppException;
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
     * @param SluggerInterface $slugger Slugger
     */
    public function __construct(
        UserService $userService,
        QuestionService $questionService,
        CategoryService $categoryService,
        AnswerService $answerService,
        SluggerInterface $slugger
    )
    {
        $this->userService = $userService;
        $this->questionService = $questionService;
        $this->categoryService = $categoryService;
        $this->answerService = $answerService;
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
     *
     * @throws AppException
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

            $this->users[] = $this->userService->updateUser($user->getId(), $formData)->getId();
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

            $this->users[] = $this->userService->updateProfile($user->getId(), $formData)->getId();
        }
    }

    /**
     * Загрузка Question Categories Fixtures
     *
     * @throws AppException
     */
    private function loadQuestionCategoriesFixtures()
    {
        // 20 категорий будет достаточно
        for ($i = 0; $i < 20; $i++) {
            $category = new CategoryCreateForm();
            $category->title = $this->faker->name;
            $category->slug = $this->slugger->slug($category->title);

            $this->categories[] = $this->categoryService->create($category)->getId();
        }
    }

    /**
     * Загрузка Question Fixtures
     *
     * @throws AppException
     */
    private function loadQuestionFixtures()
    {
        for ($i = 0; $i < 500; $i++) {
            // создание вопросы
            $formData = new QuestionCreateForm();
            $formData->userId = $this->users[array_rand($this->users)];
            $formData->categoryId = $this->categories[array_rand($this->categories)];
            $formData->title = $this->faker->text(100);
            $formData->text = $i % 2 == 0 ? $this->faker->paragraph() : '';
            $formData->createdByIp = $this->faker->ipv4;
            $questionId = $this->questionService->create($formData)->getId();

            // создание ответов к вопросу
            $count = rand(0, 20);
            for ($n = 0; $n < $count; $n++) {
                $answer = new AnswerCreateForm();
                $answer->questionId = $questionId;
                $answer->userId = $this->users[array_rand($this->users)];
                $answer->text = $this->faker->paragraph;
                $answer->createdByIp = $this->faker->ipv4;
                $this->answerService->create($answer);
            }
        }
    }
}
