<?php

namespace Drupal\test_delete_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\votingapi\VoteResultFunctionManager;
use Drupal\test_delete_field\Plugin\VotingApiWidgetManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'field_to_be_deleted_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "field_to_be_deleted_formatter",
 *   label = @Translation("Voting api formatter"),
 *   field_types = {
 *     "field_to_be_deleted"
 *   }
 * )
 */
class FieldToBeDeletedFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'readonly'      => FALSE,
      'style'         => 'default',
      'show_results'  => FALSE,
      'values'        => [],
      'show_own_vote' => FALSE,
      // Implement default settings.
    ] + parent::defaultSettings();
  }

  /**
   * Constructs an VotingApiFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings']
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      'readonly'     => [
        '#title'         => $this->t('Readonly'),
        '#type'          => 'checkbox',
        '#default_value' => $this->getSetting('readonly'),
      ],
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Styles: @styles', ['@styles' => $this->getSetting('style')]);
    $summary[] = $this->t('Readonly: @readonly', ['@readonly' => $this->getSetting('readonly') ? $this->t('yes') : $this->t('no')]);
    $summary[] = $this->t('Show results: @results', ['@results' => $this->getSetting('show_results') ? $this->t('yes') : $this->t('no')]);
    $summary[] = $this->t('Show own vote: @show_own_vote', ['@show_own_vote' => $this->getSetting('show_own_vote') ? $this->t('yes') : $this->t('no')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $entity = $items->getEntity();

    // Do not continue if the entity is being previewed.
    if (!empty($entity->in_preview)) {
      return $elements;
    }

    $field_settings = $this->getFieldSettings();
    $field_name = $this->fieldDefinition->getName();

    $vote_type = $field_settings['vote_type'];
    $vote_plugin = $field_settings['vote_plugin'];

    $show_own_vote = $this->getSetting('show_own_vote') ? TRUE : FALSE;

    $elements[] = [
      'vote_form' => [
        '#lazy_builder'       => [
          'voting_api.lazy_loader:buildForm',
          [
            $vote_plugin,
            $entity->getEntityTypeId(),
            $entity->bundle(),
            $entity->id(),
            $vote_type,
            $field_name,
            serialize($this->getSettings()),
          ],
        ],
        '#create_placeholder' => TRUE,
      ],
      'results'   => [],
    ];

    return $elements;
  }

}
