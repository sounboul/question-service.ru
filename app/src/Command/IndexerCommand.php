<?php
namespace App\Command;

use App\Elasticsearch\Model\Question;
use App\Elasticsearch\Transformers\QuestionTransformer;
use App\Repository\Question\QuestionRepository;
use App\Service\Question\QuestionService;
use JoliCode\Elastically\Client;
use Elastica\Document;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Создание/обновление поискового индекса и его заполнение
 */
class IndexerCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected static $defaultName = 'app:elasticsearch:rebuild';

    /**
     * @var Client Client
     */
    private Client $client;

    /**
     * @var QuestionRepository Question Repository
     */
    private QuestionRepository $questionRepository;

    /**
     * Конструктор
     *
     * @param string|null $name Название
     * @param Client $client Client
     * @param QuestionRepository $questionRepository Question Repository
     */
    public function __construct(
        string $name = null,
        Client $client,
        QuestionRepository $questionRepository
    )
    {
        parent::__construct($name);

        $this->client = $client;
        $this->questionRepository = $questionRepository;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDescription('Создание/обновление поискового индекса и его заполнение');
    }

    /**
     * @inheritdoc
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int
    {
        // обновление поискового индекса вопросов
        $this->questions($input, $output);

        return Command::SUCCESS;
    }

    /**
     * Создание и обновление поискового индекса вопросов
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function questions(
        InputInterface $input,
        OutputInterface $output
    )
    {
        $output->writeln("Индексация списка вопросов:");

        $indexBuilder = $this->client->getIndexBuilder();
        $newIndex = $indexBuilder->createIndex(Question::$indexName);
        $indexer = $this->client->getIndexer();

        $total = $this->questionRepository->countListingForElastic();
        $progressBar = new ProgressBar($output, $total);

        foreach ($this->questionRepository->listingForElastic() as $item) {
            /* @var \App\Entity\Question\Question $item */
            $indexer->scheduleIndex($newIndex, new Document($item->getId(), (new QuestionTransformer($item))->getModel()));

            $progressBar->advance();
        }

        $indexer->flush();

        $indexBuilder->markAsLive($newIndex, Question::$indexName);
        $indexBuilder->speedUpRefresh($newIndex);
        $indexBuilder->purgeOldIndices(Question::$indexName);

        $progressBar->finish();

        $output->writeln("\nИндексация списка вопросов завершена!");
    }
}
