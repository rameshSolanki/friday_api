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
                'base_uri' => 'https://rickandmortyapi.com/api/',
            ]
        );

        $response = $client->get(
            'character',
            [

            ]
        );

        $blogs = Json::decode($response->getBody());
        $bloglist = [];
        // echo '<pre>';
        // print_r($blogs);
        $blogs = $blogs['results'];
        foreach ($blogs as $blog) {
            $nid = $blog['id'];
            $status = $blog['status'];
            $title = $blog['name'];
            $body = $blog['species'];
            $field_blog_date = $blog['gender'];
            $field_blog_thumbnail = $blog['image'];
            $origin = $blog['origin']['name'];


            //echo $nid.'<br>';

            $bloglist[] = [
            'id' => $nid,
            'name' => $title,
            'status' => $status,
            'origin' => $origin,
            'species' => Markup::create(mb_strimwidth($body, 0, 150, '...')),
            'gender' => $field_blog_date,
            'image' => Markup::create('<img width="100px" height="100px" class="img-thumbnail" src="'.$field_blog_thumbnail.'"/>')
            ];
        }

        // echo '<pre>';
        // print_r($bloglist);
      
        $header = [
        'id' => t('#'),
        'title' => t('Name'),
        'status' => t('Status'),
        'origin' => t('Origin'),
        'content' => t('Species'),
        'date' => t('Gender'),
        'thumbnail' => t('Image'),

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
