<?php

namespace Drupal\dp_news\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\File\Entity\File;
use Drupal\user\UserInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the News entity.
 *
 * @ingroup dp_news
 *
 * @ContentEntityType(
 *   id = "news",
 *   label = @Translation("News"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\dp_news\NewsListBuilder",
 *     "views_data" = "Drupal\dp_news\Entity\NewsViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\dp_news\Form\NewsForm",
 *       "add" = "Drupal\dp_news\Form\NewsForm",
 *       "edit" = "Drupal\dp_news\Form\NewsForm",
 *       "delete" = "Drupal\dp_news\Form\NewsDeleteForm",
 *     },
 *     "access" = "Drupal\dp_news\NewsAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\dp_news\NewsHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "news",
 *   data_table = "news_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer news entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uid" = "uid",
 *     "status" = "status",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/news/{news}",
 *     "add-form" = "/news/add",
 *     "edit-form" = "/news/{news}/edit",
 *     "delete-form" = "/news/{news}/delete",
 *     "collection" = "/news/list",
 *   },
 *   field_ui_base_route = "dp_news.settings"
 * )
 */
class News extends ContentEntityBase implements NewsInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getImageFolder($base_path) {
    $date = time();
    $folder_path = date('ym', $date) . '/';
//    $folder_path = '/' . date('Y', $date) . '/' . date('m', $date) . '/';
    return $base_path . $folder_path;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $config = \Drupal::config('dp_news.settings');
    $fid = $config->get('news_image')[0];
    if ($fid && $image =  \Drupal\file\Entity\File::load($fid)) {
      $uuid = $image->uuid();
    }

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The Title of the News'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Body'))
      ->setDescription(t('The Body of the News'))
      ->setTranslatable(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'text_textfield',
        'weight' => 2,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setTranslatable(TRUE)
      ->setSetting('target_type', 'file')
      ->setSetting('file_extensions', 'jpg jpeg png')
      ->setSetting('file_directory', News::getImageFolder('images/news/'))
      ->setSetting('title_field', TRUE)
      ->setSetting('title_field_required', FALSE)
      ->setSetting('alt_field', FALSE)
      ->setSetting('alt_field_required', FALSE)
      ->setSetting('min_resolution', '640x480')
      ->setSetting('max_resolution', '1024x1024')
      ->setSetting('max_filesize', '10 Mb')
      ->setSetting('default_image', [
        'uuid' => $uuid,
        'alt' => '',
        'title' => '',
        'width' => '1600',
        'height' => '1200',
      ])
      ->setDescription(t('The image illustrated the News'))
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => 3,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'image',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['rubric'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Rubric'))
      ->setDescription(t('The Rubric of the News'))
      ->setTranslatable(TRUE)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting('handler_settings',
        array(
          'auto_create'    => TRUE,
          'target_bundles' => array(
            'rubrics' => 'rubrics'
          )))
      ->setDisplayOptions('form', array(
        'type'     => 'entity_reference_autocomplete',
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size'           => 60,
          'placeholder'    => ''
        ),
        'weight'   => 4
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 4,
      ))
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['topic'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Topic'))
      ->setDescription(t('The Topic relating to the News'))
      ->setTranslatable(TRUE)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting('handler_settings',
        array(
          'auto_create'    => TRUE,
          'target_bundles' => array(
            'topics' => 'topics'
          )))
      ->setDisplayOptions('form', array(
        'type'     => 'entity_reference_autocomplete',
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size'           => 60,
          'placeholder'    => ''
        ),
        'weight'   => 5
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 5,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('Authored by'))
        ->setDescription(t('The user ID of author of the News entity.'))
        ->setTranslatable(TRUE)
        ->setSetting('target_type', 'user')
        ->setSetting('handler', 'default')
        ->setDisplayOptions('view', [
            'label' => 'hidden',
            'type' => 'author',
            'weight' => 14,
        ])
        ->setDisplayOptions('form', [
            'type' => 'entity_reference_autocomplete',
            'weight' => 14,
            'settings' => [
                'match_operator' => 'CONTAINS',
                'size' => '60',
                'autocomplete_type' => 'tags',
                'placeholder' => '',
            ],
        ])
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);

    $fields['hot'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Hot news'))
      ->setDescription(t('A boolean indicating whether the News is Hot.'))
      ->setTranslatable(TRUE)
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 8,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['top'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Top news'))
      ->setDescription(t('A boolean indicating whether the News is Top.'))
      ->setTranslatable(TRUE)
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 9,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['rss'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Push to RSS'))
      ->setDescription(t('A boolean indicating whether the news is push to RSS feed'))
      ->setTranslatable(TRUE)
      ->setDefaultValue(TRUE)
      ->setSettings(array(
        'on_label' => 'RSS',
        'off_label' => 'Not RSS',
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'boolean',
        'weight' => 10,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'boolean_checkbox',
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['sticky'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Sticky at top of lists'))
      ->setDescription(t('A boolean indicating whether the News is sticky.'))
      ->setTranslatable(TRUE)
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 11,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Published'))
      ->setDescription(t('A boolean indicating whether the News is published.'))
      ->setTranslatable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 12,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the node was created.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 13,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 13,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the node was last edited.'))
      ->setTranslatable(TRUE);

    $fields['tags'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Tags'))
      ->setDescription(t('News tags'))
      ->setTranslatable(TRUE)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting('handler_settings',
        array(
          'auto_create'    => TRUE,
          'target_bundles' => array(
            'tags' => 'tags'
          )))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', array(
        'type'     => 'entity_reference_autocomplete_tags',
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size'           => 60,
          'placeholder'    => ''
        ),
        'weight'   => 6
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 6,
      ))
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['photo'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Photo'))
      ->setTranslatable(TRUE)
      ->setSetting('target_type', 'file')
      ->setSetting('file_extensions', 'jpg jpeg png')
      ->setSetting('file_directory', News::getImageFolder('images/newsphoto/'))
      ->setSetting('title_field', TRUE)
      ->setSetting('title_field_required', FALSE)
      ->setSetting('alt_field', FALSE)
      ->setSetting('alt_field_required', FALSE)
      ->setSetting('min_resolution', '300x200')
      ->setSetting('max_resolution', '1024x1024')
      ->setSetting('max_filesize', '10 Mb')
      ->setDescription(t('The photos to the News'))
      ->setDefaultValue('')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'settings' => ['progress_indicator' => 'bar'],
        'weight' => 7,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'image',
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }
}
