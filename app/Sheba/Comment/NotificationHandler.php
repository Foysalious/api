<?php namespace Sheba\Comment;

abstract class NotificationHandler
{
    /** @var Commentable $commentable */
    protected $commentable;
    /** @var array $accessors */
    protected $accessors;
    /** @var int $authUserId */
    protected $authUserId;
    /** @var string $authUserName */
    protected $authUserName;

    /**
     * @param Commentable $commentable
     * @return $this
     */
    public function setCommentable(Commentable $commentable)
    {
        $this->commentable = $commentable;
        return $this;
    }

    /**
     * @param array $accessors
     * @return $this
     */
    public function setAccessors(array $accessors)
    {
        $this->accessors = $accessors;
        return $this;
    }

    /**
     * @param $auth_user_name
     * @return $this
     */
    public function setAuthUserName($auth_user_name)
    {
        $this->authUserName = $auth_user_name;
        return $this;
    }

    /**
     * @param int $authUserId
     * @return $this
     */
    public function setAuthUserId($authUserId)
    {
        $this->authUserId = $authUserId;
        return $this;
    }

    abstract public function handle();
}
