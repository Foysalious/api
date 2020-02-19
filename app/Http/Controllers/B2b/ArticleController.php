<?php namespace App\Http\Controllers\B2b;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Dal\ArticleType\Contract as ArticleTypeRepositoryInterface;
use Sheba\Dal\Article\Contract as ArticleRepositoryInterface;

class ArticleController extends Controller
{
    public function getArticleTypes(Request $request, ArticleTypeRepositoryInterface $article_type_repository, ArticleRepositoryInterface $article_repository)
    {
        $article_types = $article_type_repository->getAllPublishedArticleTypes();
        foreach ($article_types as $article_type) {
            $article_type->count = $article_repository->getNumberofArticlesFilteredByArticleType($article_type->id);
        }
        return api_response($request, null, 200, ['article_types' => $article_types]);
    }

    public function getArticles(Request $request, ArticleRepositoryInterface $article_repository)
    {
        $articles = $article_repository->getAllArticlesFilteredByArticleTypes();
        if ($request->has('type')) $articles = $articles->where('article_type_id', $request->type);
        $articles = $articles->get();
        return api_response($request, null, 200, ['articles' => $articles]);
    }

    public function show($article, ArticleRepositoryInterface $article_repository, Request $request)
    {
        $article = $article_repository->find($article);
        $article->article_type = $article->articleType;
        return api_response($request, null, 200, ['article' => $article]);
    }
}