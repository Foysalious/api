<?php namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class BlogTransformer extends TransformerAbstract {
    function transform($blog) {
        $date=strtotime($blog["date"]);
        return [
            "id" => $blog["id"],
            "title" => $blog["title"]["rendered"],
            "link" => $blog["link"],
            "long_description" => strip_tags($blog["content"]["rendered"]),
            "short_description" => strip_tags($blog["excerpt"]["rendered"]),
            "created_date" => date("Y-m-d h:i:sa",$date),
        ];
    }
}