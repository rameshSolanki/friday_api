<?php

namespace Drupal\friday_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Render\Markup;

    /**
     * Provides a 'fridayAPIBlock' block.
     *
     * @Block(
     *   id = "friday_api_block",
     *   admin_label = @Translation("friday api block"),
     *   category = @Translation("friday block")
     * )
    */
class FridayAPIBlock extends BlockBase
{

 /**
  * {@inheritdoc}
 */

    public function build()
    {
        /** @var \GuzzleHttp\Client $client */
        $client = \Drupal::service('http_client_factory')->fromOptions(
            [
                'base_uri' => 'https://jsonplaceholder.typicode.com/',
            ]
        );

        $response = $client->get(
            'photos',
            [

            ]
        );

        $blogs = Json::decode($response->getBody());
        $bloglist = [];

        foreach ($blogs as $blog) {
            $nid = $blog['albumId'];
            $title = $blog['id'];
            $body = $blog['title'];
            $field_blog_date = $blog['url'];
            $field_blog_thumbnail = $blog['thumbnailUrl'];

            //echo $nid.'<br>';

            $bloglist[] = [
            'albumId' => $nid,
            'id' => $title,
            'title' => Markup::create(mb_strimwidth($body, 0, 150, '...')),
            'url' => $field_blog_date,
            'thumbnailUrl' => Markup::create('<img width="100px" height="100px" class="img-thumbnail" src="'.$field_blog_thumbnail.'"/>')
            ];
        }

        // echo '<pre>';
        // print_r($bloglist);
      
        $header = [
        'id' => t('#'),
        'title' => t('Title'),
        'content' => t('Content'),
        'date' => t('Date'),
        'thumbnail' => t('Thumbnail'),

        ];
        $build['table'] = [
        '#type' => 'table',
        // '#caption' => 'Blogs',
        '#attributes' => [
        'class' => ['table table-bordered'],
        ],
        '#header' => $header,
        '#rows' => $bloglist,
        '#empty' => t('No content has been found.'),
        ];


        return [
        '#type' => '#markup',
        '#prefix' => '<div class="table table-responsive">',
        '#markup' => render($build),
        '#suffix' => '</div>',
        ];
    }
}
