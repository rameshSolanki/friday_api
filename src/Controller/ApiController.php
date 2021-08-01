<?php

/**
 * @file
 * @author admin
 * Contains \Drupal\friday\Controller\ApiController.
 * Please place this file under your example(module_root_folder)/src/Controller/
 */

namespace Drupal\friday_api\Controller;

use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;

/**
 * Provides route responses for the Example module.
 */
class ApiController extends ControllerBase
{
  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
    public function friday_api()
    {
        
        //$config_value = \Drupal::config('friday_api.settings')->get('bio');
        //echo $config_value;
        
        $client = \Drupal::service('http_client_factory')->fromOptions([
        'base_uri' => 'http://fridayapp.cu.ma/lawncare/',
        ]);

        $response = $client->get('blogs', []);

        $blogs = Json::decode($response->getBody());
        $bloglist = [];

        foreach ($blogs as $blog) {
            $nid = $blog['nid'];
            $title = $blog['title'];
            $body = $blog['body'];
            $field_blog_date = $blog['field_blog_date'];
            $field_blog_thumbnail = $blog['field_blog_thumbnail'];
            
            $bloglist[] = [
              'nid' => $nid,
             'title' => $title,
              'body' => $body,
              'field_blog_date' => $field_blog_date,
              'field_blog_thumbnail' => $field_blog_thumbnail];
        }

        return array(
        '#theme' => 'friday_api',
        '#blogs' =>$bloglist,
        );
    }

    public function single_blog($nid)
    {
        $client = \Drupal::service('http_client_factory')->fromOptions([
        'base_uri' => 'http://fridayapp.cu.ma/lawncare/blogs/',
        ]);

        $response = $client->get($nid, []);

        $blogs = Json::decode($response->getBody());
        $bloglist = [];

        foreach ($blogs as $blog) {
            $nid = $blog['nid'];
            $title = $blog['title'];
            $body = $blog['body'];
            $field_blog_date = $blog['field_blog_date'];
            $field_blog_thumbnail = $blog['field_blog_thumbnail'];
            
            $bloglist[] = [
              'nid' => $nid,
             'title' => $title,
              'body' => $body,
              'field_blog_date' => $field_blog_date,
              'field_blog_thumbnail' => $field_blog_thumbnail];
        }

        return array(
        '#theme' => 'single_blog',
        '#blogs' =>$bloglist,
        );
    }

    public function pexels_api()
    {
        /** @var \GuzzleHttp\Client $client */
        $client = \Drupal::service('http_client_factory')->fromOptions(
            [
                'base_uri' => 'https://api.pexels.com/v1/',
                'headers' => ['Authorization' => '563492ad6f9170000100000146da1ed1e1094acf84c63920396c5041'],
            ]
        );

        $response = $client->get(
            'search',
            [
            'query' => [
                'query'=> 'Tigers',
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
            'src' => $src
            ];
        }
        

        return array(
        '#theme' => 'filter_api',
        '#blogs' =>$bloglist,
        );
    }

    public function index()
    {
        $userlist = [];
        $ids = \Drupal::entityQuery('user')
           ->condition('status', 1)
           ->condition('roles', 'administrator')
           ->execute();
        $users = User::loadMultiple($ids);
        foreach ($users as $user) {
            $username = $user->get('name')->getString();
            $mail = $user->get('mail')->getString();
            $userlist[] = ['mail' => $mail, 'username' => $username];
        }

        return array(
           '#theme' => 'friday_api',
           '#users' => $userlist
        );
    }
}
