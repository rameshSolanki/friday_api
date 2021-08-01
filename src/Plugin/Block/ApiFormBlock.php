<?php

namespace Drupal\friday_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Render\Markup;
use \Drupal\Component\Utility\Xss;

    /**
     * Provides a 'ApiFormBlock' block.
     *
     * @Block(
     *   id = "api_form_block",
     *   admin_label = @Translation("api form block"),
     *   category = @Translation("Api Form Block")
     * )
    */
class ApiFormBlock extends BlockBase
{

 /**
  * {@inheritdoc}
 */

    public function build()
    {
        $search_name = Xss::filter(\Drupal::request()->query->get('search_name'));
        
        

        /** @var \GuzzleHttp\Client $client */
        $client = \Drupal::service('http_client_factory')->fromOptions(
            [
                'base_uri' => 'https://api.pexels.com/v1/',
                'headers' => ['Authorization' => '563492ad6f9170000100000146da1ed1e1094acf84c63920396c5041'],
            ]
        );
        if ($search_name == '') {
            $response = $client->get(
                'search',
                [
                'query' => [
                'query' => 'Tigers',
                'page' => '1',
                'per_page' => '10',
                'total_results' => '20'
                ]
                ]
            );
        } else {
            $response = $client->get(
                'search',
                [
                'query' => [
                'query' => $search_name,
                'page' => '1',
                'per_page' => '10',
                'total_results' => '20'
                ]
                    ]
            );
        }
      

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
            'src' => $src
            ];
        }

       
        $form = \Drupal::formBuilder()->getForm('Drupal\friday_api\Form\FilterApi');


        return [
        '#theme' => 'api-form-block',
        '#blogs' => $bloglist,
        '#form' => $form,
        ];
    }
}
