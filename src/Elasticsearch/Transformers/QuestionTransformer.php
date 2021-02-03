<?php
namespace App\Elasticsearch\Transformers;

use App\Elasticsearch\Model\Question;
use App\Entity\Question\Question as QuestionEntity;

class QuestionTransformer implements TransformerInterface
{
    /**
     * @var QuestionEntity Entity
     */
    private QuestionEntity $entity;

    /**
     * Конструктор
     *
     * @param QuestionEntity $entity Entity
     */
    public function __construct(QuestionEntity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @inheritDoc
     */
    public function getModel(): Question
    {
        $model = new Question();
        $model->id = $this->entity->getId();

        $model->user = null;
        if ($user = $this->entity->getUser()) {
            $model->user = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
            ];
        }

        $model->category = null;
        if ($category = $this->entity->getCategory()) {
            $model->category = [
                'id' => $category->getId(),
                'title' => $category->getTitle(),
                'href' => $category->getHref(),
            ];
        }

        $model->title = $this->entity->getTitle();
        $model->text = $this->entity->getText();
        $model->href = $this->entity->getHref();
        $model->totalAnswers = $this->entity->getTotalAnswers();
        $model->createdAt = $this->entity->getCreatedAt();

        return $model;
    }
}
