<?php

namespace Drupal\dp_stop_adsense\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\taxonomy\Entity\Term;

/**
 * Defines a form that configures forms module settings.
 */
class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dp_stop_adsense.settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'dp_stop_adsense.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $config = $this->config('dp_stop_adsense.settings');
    $tags = $config->get('tags');
    $default_value = ($tags) ? Term::loadMultiple($tags) : [];
    $form['tags'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Forbidden tags'),
      '#description' => $this->t('Remove AdSense blocks on pages with these tags'),
      '#target_type' => 'taxonomy_term',
      '#selection_settings' => ['target_bundles' => ['tags']],
      '#default_value' => $default_value,
      '#tags' => TRUE,
      '#size' => 60,
      '#maxlength' => 1024,
    ];
    $form['photo'] = [
      '#type' => 'checkbox',
      '#title' => t('News with photo'),
      '#default_value' => $config->get('photo'),
      '#description' => $this->t('Remove AdSense blocks only on news pages with photo'),
    ];
    $form['days'] = [
      '#type' => 'number',
      '#title' => $this->t('Display days'),
      '#description' => $this->t('Days amount after news created and before AdSense blocks will be disabled'),
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
    $tags = array_column($values['tags'], 'target_id');
    $this->config('dp_stop_adsense.settings')
      ->set('tags', $tags)
      ->set('photo', $values['photo'])
      ->set('days', $values['days'])
      ->save();
    drupal_set_message($this->t('The configuration options have been saved.'));
  }
}