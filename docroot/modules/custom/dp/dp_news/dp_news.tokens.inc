<?php

/**
 * @file
 * Builds placeholder replacement tokens for news-related data.
 */

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\user\Entity\User;

/**
 * Implements hook_token_info().
 */
function dp_news_token_info() {
  $type = [
    'name' => t('News'),
    'description' => t('Tokens related to individual content items, or "news".'),
    'needs-data' => 'news',
  ];

  // Core tokens for news.
  $news['id'] = [
    'name' => t("Content ID"),
    'description' => t('The unique ID of the content item, or "news".'),
  ];
  $news['title'] = [
    'name' => t("Title"),
  ];
  $news['body'] = [
    'name' => t("Body"),
    'description' => t("The main body text of the news."),
  ];
  $news['summary'] = [
    'name' => t("Summary"),
    'description' => t("The summary of the news' main body text."),
  ];
  $news['summary-short'] = [
    'name' => t("Short summary"),
    'description' => t("The short summary of the news' main body text."),
  ];
  $news['langcode'] = [
    'name' => t('Language code'),
    'description' => t('The language code of the language the news is written in.'),
  ];
  $news['url'] = [
    'name' => t("URL"),
    'description' => t("The URL of the news."),
  ];
  $news['edit-url'] = [
    'name' => t("Edit URL"),
    'description' => t("The URL of the news' edit page."),
  ];

  $news['image'] = [
    'name' => t("Main image of the news"),
    'description' => t("The URL of the news' image."),
  ];


  // Chained tokens for news.
  $news['created'] = [
    'name' => t("Date created"),
    'type' => 'date',
  ];
  $news['changed'] = [
    'name' => t("Date changed"),
    'description' => t("The date the news was most recently updated."),
    'type' => 'date',
  ];
  $news['author'] = [
    'name' => t("Author"),
    'type' => 'user',
  ];

  return [
    'types' => ['news' => $type],
    'tokens' => ['news' => $news],
  ];
}

/**
 * Implements hook_tokens().
 */
function dp_news_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();

  $url_options = ['absolute' => TRUE];
  if (isset($options['langcode'])) {
    $url_options['language'] = \Drupal::languageManager()->getLanguage($options['langcode']);
    $langcode = $options['langcode'];
  }
  else {
    $langcode = LanguageInterface::LANGCODE_DEFAULT;
  }
  $replacements = [];

  if ($type == 'news' && !empty($data['news'])) {
    /** @var \Drupal\dp_news\Entity\NewsInterface $news */
    $news = $data['news'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        // Simple key values on the news.
        case 'id':
          $replacements[$original] = $news->id();
          break;
        case 'title':
          $replacements[$original] = $news->getTitle();
          break;

        case 'body':
        case 'summary':
          $translation = \Drupal::entityManager()->getTranslationFromContext($news, $langcode, ['operation' => 'dp_news_tokens']);
          if ($translation->hasField('body') && ($items = $translation->get('body')) && !$items->isEmpty()) {
            $item = $items[0];
            // If the summary was requested and is not empty, use it.
            if ($name == 'summary' && !empty($item->summary)) {
              $output = $item->summary_processed;
            }
            // Attempt to provide a suitable version of the 'body' field.
            else {
              $output = $item->processed;
              // A summary was requested.
              if ($name == 'summary') {
                // Generate an optionally trimmed summary of the body field.

                // Get the 'trim_length' size used for the 'teaser' mode, if
                // present, or use the default trim_length size.
                $display_options = entity_get_display('news', 'news', 'teaser')->getComponent('body');
                if (isset($display_options['settings']['trim_length'])) {
                  $length = $display_options['settings']['trim_length'];
                }
                else {
                  $settings = \Drupal::service('plugin.manager.field.formatter')->getDefaultSettings('text_summary_or_trimmed');
                  $length = $settings['trim_length'];
                }

                $output = text_summary($output, $item->format, $length);
              }
            }
            // "processed" returns a \Drupal\Component\Render\MarkupInterface
            // via check_markup().
            $replacements[$original] = $output;
          }
          break;

        case 'summary-short':
          $output = '';
          $trim_to = 250;
          $translation = \Drupal::entityManager()->getTranslationFromContext($news, $langcode, ['operation' => 'dp_news_tokens']);
          if ($translation->hasField('body') && ($items = $translation->get('body')) && !$items->isEmpty()) {
            $item = $items[0];
            $output = $item->processed;
            $output = text_summary($output, $item->format, $trim_to);
          }
          $replacements[$original] = $output;
          break;

        case 'langcode':
          $replacements[$original] = $news->language()->getId();
          break;

        case 'url':
          $replacements[$original] = $news->url('canonical', $url_options);
          break;

        case 'edit-url':
          $replacements[$original] = $news->url('edit-form', $url_options);
          break;

        case 'image':
          $image_url = '';
          if (!$news->get('image')->isEmpty()) {
            $image_url = $news->get('image')->entity->uri->value;
            $image_url = file_create_url($image_url);
          }
          $replacements[$original] = $image_url;
          break;

        // Default values for the chained tokens handled below.
        case 'author':
          $account = $news->getOwner() ? $news->getOwner() : User::load(0);
          $bubbleable_metadata->addCacheableDependency($account);
          $replacements[$original] = $account->label();
          break;

        case 'created':
          $date_format = DateFormat::load('medium');
          $bubbleable_metadata->addCacheableDependency($date_format);
          $replacements[$original] = format_date($news->getCreatedTime(), 'medium', '', NULL, $langcode);
          break;

        case 'changed':
          $date_format = DateFormat::load('medium');
          $bubbleable_metadata->addCacheableDependency($date_format);
          $replacements[$original] = format_date($news->getChangedTime(), 'medium', '', NULL, $langcode);
          break;
      }
    }

    if ($author_tokens = $token_service->findWithPrefix($tokens, 'author')) {
      $replacements += $token_service->generate('user', $author_tokens, ['user' => $news->getOwner()], $options, $bubbleable_metadata);
    }

    if ($created_tokens = $token_service->findWithPrefix($tokens, 'created')) {
      $replacements += $token_service->generate('date', $created_tokens, ['date' => $news->getCreatedTime()], $options, $bubbleable_metadata);
    }

    if ($changed_tokens = $token_service->findWithPrefix($tokens, 'changed')) {
      $replacements += $token_service->generate('date', $changed_tokens, ['date' => $news->getChangedTime()], $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
