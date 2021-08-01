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
                'base_uri' => 'https://api.pexels.com/v1/',
                'headers' => ['Authorization' => '563492ad6f9170000100000146da1ed1e1094acf84c63920396c5041'],
            ]
        );

        $response = $client->get(
            'curated',
            [
            'query' => [
            'page' => '1',
            'per_page' => '10',
            'total_results' => '20'
            ]
            ]
        );

        $blogs = Json::decode($response->getBody());
        $bloglist = [];
         // echo '<pre>';
         // print_r($blogs);
        $blogs = $blogs['photos'];
        foreach ($blogs as $blog) {
            $id = $blog['id'];
            $photographer = $blog['photographer'];
            $avg_color = $blog['avg_color'];
            $src = $blog['src']['medium'];


            //echo $nid.'<br>';

            $bloglist[] = [
            'id' => $id,
            'photographer' => $photographer,
            'avg_color' => $avg_color,
            'src' => Markup::create('<img class="img-thumbnail" src="'.$src.'"/>')
            ];
        }

        // echo '<pre>';
        // print_r($bloglist);
      
        $header = [
        'id' => t('#'),
        'photographer' => t('photographer'),
        'avg_color' => t('avg_color'),
        'src' => t('Image'),

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
