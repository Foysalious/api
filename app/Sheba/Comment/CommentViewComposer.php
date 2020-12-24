<?php namespace Sheba\Comment;

use Illuminate\View\View;
use Sheba\Dal\Accessor\Contract as AccessorRepo;

class CommentViewComposer
{
    private $accessors;

    public function __construct(AccessorRepo $accessors)
    {
        $this->accessors = $accessors;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('accessors', $this->accessors->getAll()->pluck('name', 'id'));
    }
}