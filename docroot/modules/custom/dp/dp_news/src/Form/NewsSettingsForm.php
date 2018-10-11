<?php

namespace Drupal\dp_news\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Class NewsSettingsForm.
 *
 * @ingroup dp_news
 */
class NewsSettingsForm extends ConfigFormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'dp_news_settings';
  }


  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'dp_news.settings',
    ];
  }

  /**
   * Defines the settings form for News entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('dp_news.settings');

    $form['news_settings']['#markup'] = 'Settings form for News entities. Manage field settings here.';

    $form['news_image'] = [
      '#type' => 'managed_file',
      '#title' => t('Image with preview'),
      '#default_value' => $config->get('news_image'),
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg'],
        'file_validate_size' => [25600000],
      ],
      '#theme' => 'image_widget',
      '#preview_image_style' => 'm',
      '#upload_location' => 'public://',
      '#required' => FALSE,
    ];
    return parent::buildForm($form, $form_state);
  }


  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if ($values['news_image']) {
      $file = File::load($values['news_image'][0]);
      if ($file instanceof File) {
        $file->setPermanent();
        $file->save();
      }
    }
    $this->config('dp_news.settings')
      ->set('news_image', $values['news_image'])
      ->save();
    parent::submitForm($form, $form_state);
    drupal_set_message($this->t('The configuration options have been saved.'));
  }
}