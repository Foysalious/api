<?php namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Sheba\Dal\BlogPost\BlogPost;

class BlogTransformer extends TransformerAbstract {
    function transform(BlogPost $blog) {
        return [
            "id" => $blog->id,
            "title" => $blog->title,
            "short_description" => $blog->short_description,
            "long_description" => $blog->long_description,
            "thumb" => $blog->thumb,
            "is_published" => $blog->is_published ? true : false,
            "target_link" => $blog->target_link,
            "created_by_name" => $blog->created_by_name,
        ];
    }
}