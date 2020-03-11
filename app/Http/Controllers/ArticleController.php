<?php namespace App\Http\Controllers;

use App\Sheba\Business\Article\LikeDislike\Creator as ArticleLikeDislikeCreator;
use App\Transformers\Business\ArticleListTransformer;
use App\Transformers\Business\ArticleTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Dal\ArticleType\Contract as ArticleTypeRepositoryInterface;
use Sheba\Dal\Article\Contract as ArticleRepositoryInterface;
use Throwable;

class ArticleController extends Controller
{
    /**
     * @param Request $request
     * @param ArticleTypeRepositoryInterface $article_type_repository
     * @return JsonResponse
     */
    public function getArticleTypes(Request $request, ArticleTypeRepositoryInterface $article_type_repository)
    {
        try {
            $article_types = $article_type_repository->getAllPublishedArticleTypes();
            return api_response($request, null, 200, ['article_types' => $article_types]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $type
     * @param Request $request
     * @param ArticleTypeRepositoryInterface $article_type_repository
     * @return JsonResponse
     */
    public function getArticles($type, Request $request, ArticleTypeRepositoryInterface $article_type_repository)
    {
        try {
            $articles = $article_type_repository->getAllPublishedArticlesFilteredByArticleType($type);
            $fractal = new Manager();
            $fractal->setSerializer(new CustomSerializer());
            $resource = new Collection($articles, new ArticleListTransformer());
            $articles = $fractal->createData($resource)->toArray()['data'];

            return api_response($request, null, 200, ['articles' => $articles]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $article
     * @param ArticleRepositoryInterface $article_repository
     * @param Request $request
     * @return JsonResponse
     */
    public function show($article, ArticleRepositoryInterface $article_repository, Request $request)
    {
        try {
            $article = $article_repository->find($article);

            $article = $article_repository->find($article);
            if (!$article) return api_response($request, null, 404, ["message" => "Article not found."]);

            $fractal = new Manager();
            $fractal->setSerializer(new CustomSerializer());
            $resource = new Item($article, new ArticleTransformer());
            $article = $fractal->createData($resource)->toArray()['data'];

            return api_response($request, null, 200, ['article' => $article]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
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
        try {
            $this->validate($request, ['is_like' => 'required|integer|between:0,1']);
            $article = $article_repository->find($article);
            if (!$article) return api_response($request, null, 404, ["message" => "Article not found."]);

            $articleLikeDislikeCreator->setArticleId($article->id)->setUserType($request->user_type)->setUserId($request->user_id)->setIsLike($request->is_like)->create();
            return api_response($request, null, 200, ['message' => 'success']);

        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
