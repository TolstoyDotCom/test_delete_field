<?php

namespace Drupal\test_delete_field\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\votingapi\Entity\VoteType;

/**
 * Plugin implementation of the 'field_to_be_deleted' field type.
 *
 * @FieldType(
 *   id = "field_to_be_deleted",
 *   label = @Translation("Field to be deleted"),
 *   description = @Translation("My Field Type"),
 *   default_widget = "field_to_be_deleted_widget",
 *   default_formatter = "field_to_be_deleted_formatter"
 * )
 */
class FieldToBeDeleted extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'vote_plugin' => '',
      'vote_type' => '',
      'status' => '',
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'result_function' => 'vote_average',
      'widget_format' => 'fivestar',
      'anonymous_window' => -2,
      'user_window' => -2,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'status';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['status'] = DataDefinition::create('integer')
      ->setLabel(t('Vote status'))
      ->setRequired(TRUE);

    $properties['value'] = DataDefinition::create('any')
      ->setLabel(t('Vote initial'))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'status' => [
          'description' => 'Whether votes are allowed on this entity: 0 = no, 1 = closed (read only), 2 = open (read/write).',
          'type' => 'int',
          'default' => 0,
        ],
      ],
      'indexes' => [],
      'foreign keys' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $values['status'] = $random->word(mt_rand(0, 1));
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = [];

    $element['vote_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Vote type'),
      '#options' => [1 => 1],
      '#required' => TRUE,
      '#default_value' => $this->getSetting('vote_type'),
      '#disabled' => $has_data,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $dateFormatter = \Drupal::service('date.formatter');
    $form = parent::fieldSettingsForm($form, $form_state);

    $unit_options = [
      300,
      900,
      1800,
      3600,
      10800,
      21600,
      32400,
      43200,
      86400,
      172800,
      345600,
      604800,
    ];

    $unit_options_form = [];
    foreach ($unit_options as $option) {
      $unit_options_form[$option] = $dateFormatter->formatInterval($option);
    }

    $unit_options_form[0] = $this->t('Immediately');
    $unit_options_form[-1] = $this->t('Never');
    $unit_options_form[-2] = $this->t('Votingapi default');

    $form['anonymous_window'] = [
      '#type' => 'select',
      '#title' => $this->t('Anonymous vote rollover'),
      '#description' => $this->t("The amount of time that must pass before two anonymous votes from the same computer are considered unique. Setting this to never will eliminate most double-voting, but will make it impossible for multiple anonymous on the same computer (like internet cafe customers) from casting votes."),
      '#options' => $unit_options_form,
      '#default_value' => $this->getSetting('anonymous_window'),
    ];

    $form['user_window'] = [
      '#type' => 'select',
      '#title' => $this->t('Registered user vote rollover'),
      '#description' => $this->t("The amount of time that must pass before two registered user votes from the same user ID are considered unique. Setting this to never will eliminate most double-voting for registered users."),
      '#options' => $unit_options_form,
      '#default_value' => $this->getSetting('user_window'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave($update) {
  }

}
