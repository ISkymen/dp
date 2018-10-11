<?php

namespace Drupal\dp_news\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for News edit forms.
 *
 * @ingroup dp_news
 */
class NewsForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\dp_news\Entity\News */
    $form = parent::buildForm($form, $form_state);
    $form['#theme'] = 'news_form';
    $user_roles = \Drupal::currentUser()->getRoles();
    $form['is_admin'] = (in_array("administrator", $user_roles)) ? TRUE : FALSE;
    $form['#attributes']['class'] = array('class' => 's-form', 's-form--news');

//    $form['marking'] = [
//      '#type' => 'fieldset',
//      '#title' => t('News\' marking'),
//      '#open' => TRUE,
//    ];
//    $form['media'] = [
//      '#type' => 'details',
//      '#title' => t('News\' media'),
//      '#open' => FALSE,
//    ];

//    foreach ($form as $key => $value) {
//      if ($form[$key]['#type'] == 'container' || $form[$key]['#type'] == 'fieldset') {
//        $form[$key]['#attributes']['class'] = array(
//          'class' =>
//            's-form__item',
//          's-form__item--' . $key,
//          's-form__item--' . $value['#type']
//        );
//      }
//    }
//
//    $form['marking']['rubric'] = $form['rubric'];
//    $form['marking']['tags'] = $form['tags'];
//    $form['media']['photo'] = $form['photo'];
//    $form['media']['field_news_video'] = $form['field_news_video'];
//    unset($form['title']['widget'][0]['value']['#description']);
//    unset($form['marking']['rubric']['widget']['#description']);

    if($this->getFormId() == 'news_add_form') {
      $form['created']['widget'][0]['value']['#default_value']  = '';
    }

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label News.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label News.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.news.canonical', ['news' => $entity->id()]);
  }

}
