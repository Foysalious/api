<?php namespace App\Http\Controllers\Help;

use App\Http\Controllers\Controller;
use App\Sheba\Business\Article\LikeDislike\Creator as ArticleLikeDislikeCreator;
use App\Transformers\Business\ArticleListTransformer;
use App\Transformers\Business\ArticleTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Redis;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Dal\ArticleType\Contract as ArticleTypeRepositoryInterface;
use Sheba\Dal\Article\Contract as ArticleRepositoryInterface;
use Sheba\Help\UserPortalMapper;
use Sheba\Portals\ArticlePortals;

class ArticleController extends Controller
{
    /**
     * @param Request $request
     * @param ArticleTypeRepositoryInterface $article_type_repository
     * @return JsonResponse
     */
    public function getArticleTypes(Request $request, ArticleTypeRepositoryInterface $article_type_repository)
    {
        $this->validate($request, ['type' => 'required|in:' . implode(',', ArticlePortals::get())]);
        // $user_type = UserPortalMapper::getPortalByUser($request->type);
        $user_type = $request->type;

        $article_types = $article_type_repository->getAllPublishedArticleTypesByUserType($user_type);
        return api_response($request, null, 200, ['article_types' => $article_types]);
    }

    /**
     * @param $type
     * @param Request $request
     * @param ArticleTypeRepositoryInterface $article_type_repository
     * @return JsonResponse
     */
    public function getArticles($type, Request $request, ArticleTypeRepositoryInterface $article_type_repository)
    {
        $this->validate($request, ['type' => 'required|in:' . implode(',', ArticlePortals::get())]);

        $articles = $article_type_repository->getAllPublishedArticlesFilteredByArticleType($type);

        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Collection($articles, new ArticleListTransformer());
        $articles = $fractal->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['articles' => $articles]);
    }

    /**
     * @param $article
     * @param ArticleRepositoryInterface $article_repository
     * @param Request $request
     * @return JsonResponse
     */
    public function show($article, ArticleRepositoryInterface $article_repository, Request $request)
    {
        $this->validate($request, ['type' => 'required|in:' . implode(',', ArticlePortals::get())]);
        $user_type = $request->type;

        $article = $article_repository->findByUserType($article, $user_type);
        if (!$article) return api_response($request, null, 404, ["message" => "Article not found."]);

        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($article, new ArticleTransformer());
        $article = $fractal->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['article' => $article]);
    }

    /**
     * @param $article
     * @param ArticleRepositoryInterface $article_repository
     * @param ArticleLikeDislikeCreator $articleLikeDislikeCreator
     * @param Request $request
     * @return JsonResponse
     */
    public function articleLikeDislike($article, ArticleRepositoryInterface $article_repository,
                                       ArticleLikeDislikeCreator $articleLikeDislikeCreator,
                                       Request $request)
    {
        $this->validate($request, ['is_like' => 'required|integer|between:0,1']);
        $article = $article_repository->find($article);
        if (!$article) return api_response($request, null, 404, ["message" => "Article not found."]);

        $articleLikeDislikeCreator->setArticleId($article->id)->setUserType($request->user_type)->setUserId($request->user_id)->setIsLike($request->is_like)->create();
        
        return api_response($request, null, 200, ['message' => 'success']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function checkAuthentication(Request $request)
    {
        $this->validate($request, ['type' => 'required|in:' . implode(',', ArticlePortals::get()), 'type_id' => 'required|integer']);
        $redis_name_space = "HelpUsers:$request->type" . '_' . $request->type_id;
        $data = Redis::get($redis_name_space);
        if (!$data) return api_response($request, null, 401);
        $data = json_decode($data);

        if ($data->id != $request->type_id) return api_response($request, null, 401);

        return api_response($request, null, 200, ['user' => $data]);
    }
}
