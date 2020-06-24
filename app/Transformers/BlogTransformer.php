<?php namespace App\Transformers;

use GuzzleHttp\Client;
use League\Fractal\TransformerAbstract;

class BlogTransformer extends TransformerAbstract {
    function transform($blog) {
        $date=strtotime($blog["date"]);
        return [
            "id" => $blog["id"],
            "title" => $blog["title"]["rendered"],
            "link" => $blog["link"],
            "category" => $this->getCategory($blog["_links"]),
            "image" => $this->getTitleImage($blog["_links"]),
            "short_description" => strip_tags($blog["excerpt"]["rendered"]),
            "created_date" => date("Y-m-d h:i:sa",$date),
        ];
    }
    private function getTitleImage($link) {
        $url = $link["wp:featuredmedia"][0]["href"];
        $response = (new Client())->get($url)->getBody()->getContents();
        $response = json_decode($response, 1);
        if($response["guid"]["rendered"]) {
            return $response["guid"]["rendered"];
        }
        return null;
    }

    private function getCategory($link) {
        $url = $link["wp:term"][0]["href"];
        $response = (new Client())->get($url)->getBody()->getContents();
        $response = json_decode($response, 1);
        if($response[0]["name"]) {
            return $response[0]["name"];
        }
        return null;
    }
}