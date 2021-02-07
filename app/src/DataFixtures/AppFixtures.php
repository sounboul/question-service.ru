<?php
namespace App\DataFixtures;

use App\Dto\Question\AnswerCreateForm;
use App\Dto\Question\CategoryCreateForm;
use App\Dto\Question\QuestionCreateForm;
use App\Dto\User\UserCreateForm;
use App\Dto\User\UserUpdateProfileForm;
use App\Dto\User\UserRegistrationForm;
use App\Dto\User\UserUpdateForm;
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
            $formData = new UserCreateForm();
            $formData->email = $email;
            $formData->password = '1234567890';
            $formData->username = 'Ivan';
            $formData->about = 'Всем привет!';
            $formData->roles = ["ROLE_ADMIN"];

            $this->users[] = $this->userService->create($formData)->getId();
        }

        // и несколько сотен обычных пользователей
        for ($i = 0; $i < 300; $i++) {
            $formData = new UserCreateForm();
            $formData->email = $this->faker->email;
            $formData->password = $this->faker->password(8);
            $formData->username = $this->faker->name;
            $formData->about = $this->faker->realText();

            $this->users[] = $this->userService->create($formData)->getId();
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
            $category->title = $this->faker->company;
            $category->slug = $this->slugger->slug($category->title);
            $category->description = $this->faker->realText(1000);

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
        for ($i = 0; $i < 1000; $i++) {
            // создание вопросы
            $formData = new QuestionCreateForm();
            $formData->userId = $this->users[array_rand($this->users)];
            $formData->categoryId = $this->categories[array_rand($this->categories)];
            $formData->title = rtrim($this->faker->text(100), '.').'?';
            $formData->text = $i % 2 == 0 ? $this->getRandomText(mt_rand(200, 1000)) : '';
            $formData->createdByIp = $this->faker->ipv4;
            $questionId = $this->questionService->create($formData)->getId();

            // создание ответов к вопросу
            $count = rand(0, 20);
            for ($n = 0; $n < $count; $n++) {
                $answer = new AnswerCreateForm();
                $answer->questionId = $questionId;
                $answer->userId = $this->users[array_rand($this->users)];
                $answer->text = $this->getRandomText(mt_rand(300, 800));
                $answer->createdByIp = $this->faker->ipv4;
                $this->answerService->create($answer);
            }
        }
    }

    /**
     * @param int $length
     * @return string Случайный текст
     */
    private function getRandomText(int $length = 500): string
    {
        do {
            $text = $this->faker->realText(5000);
        } while(mb_strlen($text) < $length);

        return mb_substr($text, 0, $length);
    }
}
