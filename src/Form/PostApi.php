<?php
/**
 * @file
 * Contains \Drupal\friday_api\Form\PostApi.
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

class PostApi extends FormBase
{

  /**
   * Current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
    protected $currentUser;

  /**
   * Node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
    protected $nodeManager;


  /**
   * {@inheritdoc}
   */
    public function __construct(
        EntityTypeManager $entity_type_manager,
        AccountProxyInterface $current_user
    ) {
        $this->currentUser = $current_user;
        $this->nodeManager = $entity_type_manager->getStorage('node');
    }

  /**
   * {@inheritdoc}
   */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity_type.manager'),
            $container->get('current_user')
        );
    }

  /**
   * {@inheritdoc}
   */
    public function getFormId()
    {
        return 'post_api_form';
    }

  /**
   * {@inheritdoc}
   */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $test_arr = ['One', 'Two', 'Three', 'Four', 'Five'];

        if (!isset($current_time)) {
                $current_time = \Drupal::time()->getCurrentTime();
                $date_output = date('d-m-Y h:i:s A', $current_time);
        }

        if (!$form_state->get('number_of_fields')) {
            $form_state->set('number_of_fields', 1);
        }
        if ($form_state->isRebuilding()) {
              $name = $form_state->getValue('ip_address');
              $form['header'] = [
                '#type' => 'container',
                '#attributes' => ['class' => ['form-header-message', 'alert', 'alert-success']],
                'message' => [
                  '#markup' => $this->t('Thank you for submitting this form, %name!', ['%name' => $name]),
                ],
              ];
        }

        $form['message'] = [
        '#type' => 'markup',
        '#markup' => '<div id="result-message"></div>'
        ];
        
        $form['#prefix'] = '<div class="form jumbotron">';

        $form['title'] =  [
        '#type' => 'textfield',
        '#default_value' =>$date_output,
        '#title' => $this->t('Enter your title here!'),
        // '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12']
        ];

        $form['ip_address'] =  [
        '#type' => 'textfield',
        //'#default_value' =>\Drupal::request()->getClientIp(),
        '#title' => $this->t('Enter your IP here!'),
        // '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12']
        ];
    
        
        $form['test_arr'] = [
            '#type' => 'select',
            '#title' => $this->t('Select any one.'),
            // '#required' => true,
            '#options' => array_combine($test_arr, $test_arr),
        ];

        $form['picture'] = array(
        '#title' => t('picture'),
        '#description' => $this->t('Chossir Image gif png jpg jpeg'),
        '#type' => 'managed_file',
        // '#required' => true,
        '#upload_location' => 'public://images/',
        '#upload_validators' => array(
        'file_validate_extensions' => array('gif png jpg jpeg')),
        );

        $form['#tree'] = true;
        $form['container'] = [
        '#type' => 'container',
        '#id' => 'replace-me',
        ];

        for ($i = 0; $i < $form_state->get('number_of_fields'); $i++) {
            $form['container'][$i] = [
            'my_text' => [
            '#type' => 'textfield',
            '#title' => $this->t('Any text'),
            ],
            ];
        }

        $form['add'] = [
        '#type' => 'submit',
        '#value' => t('Add another'),
        '#submit' => [[$this, 'addTextFieldCallback']],
        '#ajax' => [
        'callback' => [$this, 'ajaxReplaceMeCallback'],
        'wrapper' => 'replace-me',
        '#buttom_type' => 'primary'
        ],
        ];

        if (null !== $form_state->get('submitted')) {
            // My custom markup to be displayed on form submit
            $form['myowntext_display'] = array
            (
            '#markup' => $this->t('VIEW LISTING'),
            '#prefix' => '<h1>',
            '#suffix' => '</h1>',
            );
        }


        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
         '#attributes' => array(
        'onclick' => 'javascript:var s=this;setTimeout(function(){s.value="Saving...";s.disabled=true;},1);',
         ),
         '#buttom_type' => 'primary'
      // '#attributes' => array('class' => array('visually-hidden')),
        ];

        $form['#theme'] = 'add_visitor_form';
        $form['#suffix'] = '</div>';
        // $form['#attached']['library']= [
        // 'core/jquery',
        // 'core/drupal.ajax',
        // 'core/jquery.once',
        // 'core/jquery.form',
        // ];
        return $form;
    }


    public function addTextFieldCallback(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('number_of_fields', $form_state->get('number_of_fields') + 1);
        $form_state->setRebuild();
    }

    public function ajaxReplaceMeCallback(array &$form, FormStateInterface $form_state)
    {
        return $form['container'];
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
    
        // $newMail = \Drupal::service('plugin.manager.mail');
        // $params['ip_address'] = $form_state->getValue('ip_address');
        // $params['created'] = $date_output;
        // $newMail->mail('add_visitor_friday_form', 'registerMail', 'solankiramesh48@gmail.com', 'en', $params, $reply = null, $send = true);
        $serialized_entity = json_encode([
        'title' => [['value' => $form_state->getValue('test_arr')]],
        'body' => [['value' => $form_state->getValue('title')]],
        '_links' => ['type' => [
        'href' => 'http://fridayapp.cu.ma/lawncare/rest/type/node/task'
        ]],
        ]);


        try {
               $response = \Drupal::httpClient()
               ->post(
                   'http://fridayapp.cu.ma/lawncare/entity/node?_format=json',
                   [
                   'auth' =>
                   [
                   'admin', 'drupal@#007'
                   ],
        
                   'body' => $serialized_entity,
                   'headers' => [
                   'Content-Type' => 'application/hal+json',
                   'X-CSRF-Token' => '8Js5xygCPT4glAh-T75SaokDJNhUugrJbkntbmHJ6_c'
                   ],
                   ]
               );
        } catch (\Exception $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse()->getBody()->getContents();
                echo "<pre>";
                print_r($response);
                echo "</pre>";
            } else {
                $response = $e->getResponse()->getBody()->getContents();
                echo "<pre>";
                print_r($response);
                echo "</pre>";
            }
        }
    
        $form_state->setUserInput([]);
        $form_state->setRebuild();
       
        // $messenger = \Drupal::messenger();
        // $messenger->addMessage($this->t('Successfully submitted'));
        // $url = new Url('friday.visitorform');
        // $response = new RedirectResponse($url->toString());
        // $response->send();
        // $form_state->set('submitted', true);
        // $form_state->setRebuild(true);
    }
}
