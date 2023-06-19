<?php

namespace Drupal\test_delete_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\test_delete_field\Plugin\VotingApiWidgetManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Plugin implementation of the 'field_to_be_deleted_widget' widget.
 *
 * @FieldWidget(
 *   id = "field_to_be_deleted_widget",
 *   label = @Translation("Field to be deleted"),
 *   field_types = {
 *     "field_to_be_deleted"
 *   }
 * )
 */
class FieldToBeDeletedWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return ['show_initial_vote' => 0];
  }

  /**
   * The user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Constructs the VotingApiWidget object.
   *
   * @param string $plugin_id
   *   The plugin ID for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, AccountInterface $account) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->account = $account;
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
      $configuration['third_party_settings'],
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['show_initial_vote'] = [
      '#type' => 'select',
      '#options' => [
        0 => $this->t("Don't show initial vote"),
        1 => $this->t('Show initial vote'),
      ],
      '#default_value' => $this->getSetting('show_initial_vote'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $entity = $items->getEntity();
    $element['status'] = [
      '#type' => 'radios',
      '#title' => $this->fieldDefinition->getLabel(),
      '#default_value' => isset($items->getValue('status')[0]['status']) ? $items->getValue('status')[0]['status'] : 1,
      '#options' => [
        1 => $this->t('Open'),
        0 => $this->t('Closed'),
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t(
      'Show initial vote: @show_initial_vote',
      ['@show_initial_vote' => $this->getSetting('show_initial_vote') ? $this->t('yes') : $this->t('no')]
    );

    return $summary;
  }

}
