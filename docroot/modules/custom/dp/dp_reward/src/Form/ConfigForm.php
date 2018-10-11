<?php

namespace Drupal\dp_reward\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dp_reward_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'dp_reward.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dp_reward.settings');

    $form['google_settings'] = [
      '#type' => 'details',
      '#title' => t('Google settings'),
      '#open' => FALSE,
    ];
    $form['google_settings']['google_client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Client ID'),
      '#default_value' => $config->get('google_client_id'),
      '#placeholder' => '363750735341-60recgmfguportgn3n071dllhnvi4hpl.apps.googleusercontent.com'
    ];
    $form['google_settings']['google_client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Client secret'),
      '#default_value' => $config->get('google_client_secret'),
      '#placeholder' => 'V9Ue65LWAQqqyC4dgxT0MpLz'
    ];
    $form['google_settings']['google_redirect_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google redirect URI'),
      '#default_value' => $config->get('google_redirect_uri'),
      '#placeholder' => 'https://example.com'
    ];
    $form['google_settings']['google_account_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AdSense Account ID'),
      '#default_value' => $config->get('google_account_id'),
      '#placeholder' => 'pub-4368230766331591'
    ];
    $form['google_settings']['google_url_channel_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AdSense Channel Name'),
      '#default_value' => $config->get('google_url_channel_name'),
      '#placeholder' => 'channel_name'
    ];
    $form['update_settings'] = [
      '#type' => 'details',
      '#title' => t('Update frequency settings'),
      '#open' => FALSE,
    ];
    $form['update_settings']['exchange_timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Exchange frequency, hours'),
      '#description' => $this->t('Exchange rate frequency update in hours'),
      '#default_value' => $config->get('exchange_timeout'),
      '#min' => "1",
      '#max' => "24",
      '#step' => '1'
    ];
    $form['update_settings']['profit_timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Profit frequency, minutes'),
      '#description' => $this->t('Profit frequency update in minutes'),
      '#default_value' => $config->get('profit_timeout'),
      '#min' => "0",
      '#max' => "360",
      '#step' => '5'
    ];
    $form['update_settings']['cron'] = [
      '#type' => 'checkbox',
      '#title' => t('Use cron'),
      '#default_value' => $config->get('cron'),
      '#description' => $this->t('Get profit data by cron for fast output'),
    ];
    $form['ratio_settings'] = [
      '#type' => 'details',
      '#title' => t('Reward ratio settings'),
      '#open' => FALSE,
    ];
    $form['ratio_settings']['editors_ratio'] = [
      '#type' => 'number',
      '#title' => $this->t('Editors ratio'),
      '#default_value' => $config->get('editors_ratio'),
      '#min' => "0",
      '#max' => "1",
      '#step' => '0.05'
    ];
    $form['ratio_settings']['views_ratio'] = [
      '#type' => 'number',
      '#title' => $this->t('Views ratio'),
      '#default_value' => $config->get('views_ratio'),
      '#min' => "0",
      '#max' => "1",
      '#step' => '0.05'
    ];
    $form['user_settings'] = [
      '#type' => 'details',
      '#title' => t('User\'s settings'),
      '#open' => FALSE,
    ];
    $form['user_settings']['status_timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Timeout activity between online/offline user\'s status, min'),
      '#default_value' => $config->get('status_timeout'),
      '#min' => "5",
      '#max' => "60",
      '#step' => '5'
    ];
    $form['user_settings']['active_timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Timeout activity for user\'s is active, days'),
      '#default_value' => $config->get('active_timeout'),
      '#min' => "1",
      '#max' => "931",
      '#step' => '1'
    ];
    $form['dev_settings'] = [
      '#type' => 'details',
      '#title' => t('Development settings'),
      '#open' => FALSE,
    ];
    $form['dev_settings']['yandex_json_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Yandex Json URL'),
      '#default_value' => $config->get('yandex_json_url'),
      '#placeholder' => 'Leave empty if want use real data'
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('dp_reward.settings')
      ->set('editors_ratio', $values['editors_ratio'])
      ->set('views_ratio', $values['views_ratio'])
      ->set('google_client_id', $values['google_client_id'])
      ->set('google_client_secret', $values['google_client_secret'])
      ->set('google_redirect_uri', $values['google_redirect_uri'])
      ->set('google_account_id', $values['google_account_id'])
      ->set('google_url_channel_name', $values['google_url_channel_name'])
      ->set('exchange_timeout', $values['exchange_timeout'])
      ->set('profit_timeout', $values['profit_timeout'])
      ->set('cron', $values['cron'])
      ->set('status_timeout', $values['status_timeout'])
      ->set('active_timeout', $values['active_timeout'])
      ->set('yandex_json_url', $values['yandex_json_url'])
      ->save();
    parent::submitForm($form, $form_state);
  }

}