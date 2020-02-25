<?php namespace App\Http\Controllers\B2b;

use App\Transformers\Business\ArticleListTransformer;
use App\Transformers\Business\ArticleTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Dal\ArticleType\Contract as ArticleTypeRepositoryInterface;
use Sheba\Dal\Article\Contract as ArticleRepositoryInterface;

class ArticleController extends Controller
{
    public function getArticleTypes(Request $request, ArticleTypeRepositoryInterface $article_type_repository)
    {
        $article_types = $article_type_repository->getAllPublishedArticleTypes();
        return api_response($request, null, 200, ['article_types' => $article_types]);
    }

    public function getArticles($type, Request $request, ArticleTypeRepositoryInterface $article_type_repository)
    {
        $articles = $article_type_repository->getAllPublishedArticlesFilteredByArticleType($type);
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Collection($articles, new ArticleListTransformer());
        $articles = $fractal->createData($resource)->toArray()['data'];
        return api_response($request, null, 200, ['articles' => $articles]);
    }

    public function show($article, ArticleRepositoryInterface $article_repository, Request $request)
    {
        $article = $article_repository->find($article);
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($article, new ArticleTransformer());
        $article = $fractal->createData($resource)->toArray()['data'];
        return api_response($request, null, 200, ['article' => $article]);
    }
}