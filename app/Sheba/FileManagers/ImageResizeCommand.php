<?php namespace Sheba\FileManagers;


use App\Console\Commands\Command as BaseCommand;
use App\Models\OfferShowcase;
use Sheba\Dal\Category\Category;
use App\Models\Slide;

class ImageResizeCommand extends BaseCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'sheba:resize-uploaded-image {--categories=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resize and save already uploaded images.';

    public function handle()
    {
        $this->handleCategories();
        $this->handleSliders();
        $this->handleOffers();
        $this->info('Done.');
    }

    private function handleCategories()
    {
        if ($categories = $this->option('categories')) {
            $categories = explode(',', $categories);
            $this->resizeCategoryImages(Category::whereIn('id', $categories)->get());
        } else {
            $this->resizeCategoryImages(Category::all());
        }
    }

    private function handleSliders()
    {
        if ($slides = $this->option('slides')) {
            $slides = explode(',', $slides);
            $this->resizeSlides(Slide::whereIn('id', $slides)->get());
        } else {
            $this->resizeSlides(Slide::all());
        }
    }

    private function handleOffers()
    {
        if ($offers = $this->option('offers')) {
            $offers = explode(',', $offers);
            $this->resizeOfferImages(OfferShowcase::whereIn('id', $offers)->get());
        } else {
            $this->resizeOfferImages(OfferShowcase::all());
        }
    }

    private function resizeCategoryImages($categories)
    {
        $category_sizes = config('image_sizes.category');
        $icon_png_size = $category_sizes['icon_png'];
        $thumb_size = $category_sizes['thumb'];
        $app_thumb_size = $category_sizes['app_thumb'];
        $app_banner_size = $category_sizes['app_banner'];

        foreach ($categories as $category) {
            if ($category->app_banner) resizeAndSaveImage($category->app_banner, $app_banner_size['height'], $app_banner_size['width']);
            if ($category->thumb) resizeAndSaveImage($category->thumb, $thumb_size['height'], $thumb_size['width']);
            if ($category->app_thumb) resizeAndSaveImage($category->app_thumb, $app_thumb_size['height'], $app_thumb_size['width']);
            if ($category->icon_png) resizeAndSaveImage($category->icon_png, $icon_png_size['height'], $icon_png_size['width']);
            if ($category->icon_png_hover) resizeAndSaveImage($category->icon_png_hover, $icon_png_size['height'], $icon_png_size['width']);
        }
    }

    private function resizeSlides($slides)
    {
        $size = config('image_sizes.slider.app_banner');
        foreach ($slides as $slide) {
            if ($slide->small_image_link) resizeAndSaveImage($slide->small_image_link, $size['height'], $size['width']);
        }
    }

    private function resizeOfferImages($offers)
    {
        $size = config('image_sizes.offer.app_banner');
        foreach ($offers as $offer) {
            if ($offer->app_banner) resizeAndSaveImage($offer->app_banner, $size['height'], $size['width']);
        }
    }
}
