<?php

namespace App\Controller;

use App\Model\ArticleRepository;
use App\Services\LinkResolverService;
use Carbon\Carbon;
use Twig\Environment;
use Prismic\Api;
use Prismic\Dom\RichText;
use Prismic\LinkResolver;
use Prismic\Predicates;

class HomeController
{
    /**
     * @var ArticleRepository
     */
    private $repository;

    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(ArticleRepository $repository, Environment $twig)
    {
        $this->repository = $repository;
        $this->twig = $twig;
    }

    const BUYING_GUIDE = 'ZddCHhEAAJYkLUvs';

    public function index()
    {
        $categories = $this->getCategories();
        $featuredBlogPost = $this->getLatestFeaturedBlogPost();
        $buyingGuidePosts = $this->getBlogPostsByCategory(self::BUYING_GUIDE);

        echo $this->twig->render('home.twig', [
            'categories' => $categories,
            'featuredBlogPost' => $featuredBlogPost,
            'buyingGuidePosts' => $buyingGuidePosts,
        ]);
    }

    public function get($uid)
    {
      $blogPostResponse = $this->prismicCall(
        Predicates::at('my.blog_post.uid', $uid)
      );

      $blogPostResults = $blogPostResponse->results[0];
      $linkResolver = new LinkResolverService();

      $author = $this->getAuthor($blogPostResults->data->author_section->id);
      $category = $this->getCategory($blogPostResults->data->category->id);

      $uid = $blogPostResults->uid;
      $title = RichText::asHtml($blogPostResults->data->title, $linkResolver);
      $image = $blogPostResults->data->image->url;
      $content = RichText::asHtml($blogPostResults->data->content, $linkResolver);
      $lastPublicationDate = $blogPostResults->last_publication_date;

      $productCategories = [];
      foreach ($blogPostResults->data->product_categories as $productCategory) {
          $productCategoryData = $this->getProductCategoryById($productCategory->product_group->id);

          $productCategories[] = $productCategoryData;
      }

      $blogPost = [
        'uid' => $uid,
        'title' => $title,
        'image' => $image,
        'content' => $content,
        'lastPublicationDate' => Carbon::parse($lastPublicationDate)->isoFormat('MMMM DD, YYYY'),
        'author' => $author,
        'readingMins' => $this->getReadingMins($content),
        'categoryName' => $category['name'],
        'productCategories' => $productCategories
      ];

      echo $this->twig->render('article.twig', [
        'blogPost'=> $blogPost
      ]);
    }

    public function getCategories()
    {
        $categories = json_decode(json_encode($this->prismicCall(Predicates::at('document.type', 'category'))),true);

        return array_map(function($category){
            return [
                'id' => $category['id'],
                'name' => $category['data']['name'][0]['text']
            ];
            
        }, $categories['results']);
    }

    public function getAuthor($id)
    {
        $authorResponse = $this->prismicCall(Predicates::at('document.id', $id));

        $linkResolver = new LinkResolverService();
        $authorResult = $authorResponse->results[0];

        $name = RichText::asText($authorResult->data->name, $linkResolver);
        $image = $authorResult->data->author_image->url;
        $summary = RichText::asHtml($authorResult->data->summary, $linkResolver);

        return [
          'name' => $name,
          'image' => $image,
          'summary' => $summary
        ];
    }

    public function getCategory($id)
    {
        $categoryResponse = $this->prismicCall(Predicates::at('document.id', $id));

        $linkResolver = new LinkResolverService();
        $categoryResult = $categoryResponse->results[0];

        $name = RichText::asText($categoryResult->data->name, $linkResolver);
        $image = $categoryResult->data->image->url;

        return [
          'name' => $name,
          'image' => $image,
        ];
    }

    public function getLatestFeaturedBlogPost()
    {
        $featuredBlogPostResponse = $this->prismicCall(
            Predicates::at('my.blog_post.featured', true),
            ['pageSize' => 1, 'page' => 1, 'orderings' => '[document.first_publication_date desc]']
        );

        $featuredBlogPostResult = $featuredBlogPostResponse->results[0];

        $linkResolver = new LinkResolverService();

        $title = RichText::asHtml($featuredBlogPostResult->data->title, $linkResolver);
        $image = $featuredBlogPostResult->data->image->url;
        $contentSummary = RichText::asText($featuredBlogPostResult->data->content, $linkResolver);
        $uid = $featuredBlogPostResult->uid;

        return [
          'uid' => $uid,
          'title' => $title,
          'image' => $image,
          'contentSummary' => substr($contentSummary, 0, 150)
        ];
    }

    public function getByCategory($id)
    {
      if($id == "all"){
        echo json_encode($this->getAllBlogPosts());
      }else{
        echo json_encode($this->getBlogPostsByCategory($id));
      }
    }

    public function getBlogPostsByCategory($categoryId)
    {
        $blogPostsResponse = $this->prismicCall(
            Predicates::at('my.blog_post.category', $categoryId),
            ['pageSize' => 10, 'page' => 1, 'orderings' => '[document.first_publication_date desc]']
        );

        $blogPostsResults = $blogPostsResponse->results;
        $linkResolver = new LinkResolverService();
        $blogPosts = [];

        if(count($blogPostsResults) == 0){
          return [];
        }

        foreach ($blogPostsResults as $blogPost) {
          $uid = $blogPost->uid;
          $title = $blogPost->data->title[0]->text;
          $image = $blogPost->data->image->url;
          $contentSummary = RichText::asText($blogPost->data->content, $linkResolver);
          $publicationDate = $blogPost->first_publication_date;
  
          $blogPosts[] = [
            'uid' => $uid,
            'title' => $title,
            'image' => $image,
            'contentSummary' => substr($contentSummary, 0, 75),
            'publicationDate' => Carbon::parse($publicationDate)->isoFormat('MMMM DD, YYYY')
          ];
        }

        return $blogPosts;
    }

    public function getAllBlogPosts()
    {
        $blogPostsResponse = $this->prismicCall(
            Predicates::at('document.type', 'blog_post'),
            ['pageSize' => 10, 'page' => 1, 'orderings' => '[document.first_publication_date desc]']
        );

        $blogPostsResults = $blogPostsResponse->results;
        $linkResolver = new LinkResolverService();
        $blogPosts = [];

        if(count($blogPostsResults) == 0){
          return [];
        }

        foreach ($blogPostsResults as $blogPost) {
          $uid = $blogPost->uid;
          $title = $blogPost->data->title[0]->text;
          $image = $blogPost->data->image->url;
          $contentSummary = RichText::asText($blogPost->data->content, $linkResolver);
          $publicationDate = $blogPost->first_publication_date;

          $blogPosts[] = [
            'uid' => $uid,
            'title' => $title,
            'image' => $image,
            'contentSummary' => substr($contentSummary, 0, 75),
            'publicationDate' => Carbon::parse($publicationDate)->isoFormat('MMMM DD, YYYY'),
          ];
        }

        return $blogPosts;
    }

    public function getProductCategoryById($id)
    {
        $productCategoryResponse = $this->prismicCall(Predicates::at('document.id', $id));

        $productCategoryResults = $productCategoryResponse->results[0];

        $linkResolver = new LinkResolverService();

        $name = $productCategoryResults->data->name[0]->text;
        $productGroupId = $productCategoryResults->data->product_group_id;        

        return [
          'name' => $name,
          'productGroupId' => $productGroupId,
        ];
    }


    private function prismicCall($query, $options = [])
    {
        $token = "MC5aZGJhTWhFQUFDTUFLN29q.77-977-977-9Chs4X--_vQHvv71P77-977-977-9NO-_ve-_vQw2YUDvv73vv73vv73vv71g77-9A--_ve-_vS1j";
        $api = Api::get("https://villvay-test.cdn.prismic.io/api/v2", $token);
        return $api->query($query, $options);
    }

    private function getReadingMins($content)
    {
      $word_count = str_word_count(strip_tags($content));
	    $words_per_minute = 200;

	    return ceil( $word_count / $words_per_minute );
    }
}
