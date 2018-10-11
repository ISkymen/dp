<?php
/**
 * Created by PhpStorm.
 * User: sky
 * Date: 16.07.2017
 * Time: 2:14
 */

namespace Drupal\dp_vars\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dp_vars_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'dp_vars.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $config = $this->config('dp_vars.settings');
    $form['head'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Head script'),
      '#default_value' => $config->get('head'),
    );
    $form['body'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Body top'),
      '#default_value' => $config->get('body'),
    );

    $form['variables'] = [
      '#type' => 'details',
      '#title' => t('Variables'),
      '#open' => FALSE,
    ];
    $form['variables']['copyright'] = [
      '#type' => 'textarea',
      '#title' => t('Copyright'),
      '#default_value' => $config->get('copyright'),
      '#rows' => 5,
      '#description' => t('Copyright text.'),
    ];
    $form['variables']['counters'] = [
      '#type' => 'textarea',
      '#title' => t('Counters'),
      '#default_value' => $config->get('counters'),
      '#rows' => 5,
      '#description' => t('Counters code.'),
    ];
    $form['days'] = [
      '#type' => 'number',
      '#title' => $this->t('News days'),
      '#description' => $this->t('News days in topics'),
      '#default_value' => $config->get('days'),
      '#min' => "0",
      '#max' => "31",
      '#step' => '1'
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('dp_vars.settings')
      ->set('head', $values['head'])
      ->set('body', $values['body'])
      ->set('copyright', $values['copyright'])
      ->set('counters', $values['counters'])
      ->set('days', $values['days'])
      ->save();
    drupal_set_message($this->t('The configuration options have been saved.'));
  }
}