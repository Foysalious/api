<?php namespace App\Sheba\Business\Article\LikeDislike;

use Sheba\Dal\ArticleLikeDislike\EloquentImplementation as ArticleLikeDislikeRepository;

class Creator
{
    private $articleId;
    private $userType;
    private $userId;
    private $isLike;
    private $articleLikeDislikeRepository;
    private $data;

    public function __construct(ArticleLikeDislikeRepository $article_like_dislike_repository)
    {
        $this->articleLikeDislikeRepository = $article_like_dislike_repository;
        $this->userType = null;
        $this->userId = null;
        $this->data = [];
    }

    public function setArticleId($article_id)
    {
        $this->articleId = $article_id;
        return $this;
    }

    public function setUserType($user_type)
    {
        $this->userType = $user_type;
        return $this;
    }

    public function setUserId($user_id)
    {
        $this->userId = $user_id;
        return $this;
    }

    public function setIsLike($is_like)
    {
        $this->isLike = $is_like;
        return $this;
    }

    private function makeData()
    {
        $this->data['article_id'] = $this->articleId;
        $this->data['user_type'] = $this->userType;
        $this->data['user_id'] = $this->userId;
        $this->data['is_like'] = $this->isLike;
    }

    public function create()
    {
        $this->makeData();
        $this->articleLikeDislikeRepository->create($this->data);
    }
}