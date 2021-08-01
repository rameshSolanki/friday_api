<?php
/**
 * @file
 * Contains \Drupal\friday_api\Form\FilterApi.
 */

namespace Drupal\friday_api\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Database\Connection;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Url;
use Drupal\Core\Routing;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Render\Markup;
use \Drupal\Component\Utility\Xss;

class FilterApi extends FormBase
{

  /**
   * {@inheritdoc}
   */
    public function getFormId()
    {
        return 'filter_api_form';
    }

  /**
   * {@inheritdoc}
   */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
            $options = ['Nature', 'Tigers', 'People'];
          $options = array_combine($options, $options);

        $search_name = Xss::filter(\Drupal::request()->query->get('search_name'));
        $query = $form_state->getValue('query');

        $form['#prefix'] = '<div class="form jumbotron">';

        $form['query'] = [
        '#type' => 'select',
        '#title' => 'Select Categories',
        '#empty_option' => $this->t('- Select Categories -'),
        '#options' => $options,
        '#default_value' => isset($search_name) ? $search_name : 'Tigers',
        ];


        $form['actions']['#type'] = 'actions';

        $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        '#buttom_type' => 'primary'
        ];

        
        $form['#suffix'] = '</div>';
        return $form;
    }

  /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
    }

  /**
   * {@inheritdoc}
   */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
    
        $key = $form_state->getValue('query');
        $val = $form['query']['#options'][$key];


        $params['query'] = [
        'search_name' => $val,
        ];

        $form_state->setRedirectUrl(Url::fromUri('internal:' . '/filter-api', $params));
        $messenger = \Drupal::messenger();
        $messenger->addMessage($this->t('Successfully submitted ' .$val));
        //$url = new Url('friday.filterApi');
        //$response = new RedirectResponse($url->toString());
        //$response->send();
        $form_state->set('submitted', true);
        //$form_state->setRebuild(true);
    }
}
