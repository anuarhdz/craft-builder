<?php

namespace anuarhdz\builder\console\controllers;

use Craft;
use craft\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Json;
use craft\helpers\Console;
use anuarhdz\builder\Builder;

/**
 * Import Controller controller
 */
class ImportController extends Controller
{
    /**
     * Importa la estructura desde JSON.
     * Si se pasa un path, se carga desde archivo; si no, desde los settings del plugin.
     *
     * @return int
     */
    public function actionJson(): int
    {
        $settings = Builder::getInstance()->getSettings();
        $FIELD_CONFIGS = $settings->FIELD_CONFIGS ?? [];
        $ENTRY_TYPE_CONFIGS = $settings->ENTRY_TYPE_CONFIGS ?? [];
        $SECTION_CONFIGS = $settings->SECTION_CONFIGS ?? [];

        $this->stdout("✔️  Importación finalizada." . PHP_EOL);
        return ExitCode::OK;
    }

    /**
     * Deletes fields based on the current FIELD_CONFIGS settings.
     *
     * This action retrieves the field configurations from the plugin settings.
     * If there are no fields configured, it outputs a message and exits successfully.
     * Otherwise, it proceeds to delete the configured fields.
     *
     * @return void
     */
    public function actionDeleteFields(): void
    {
        $settings = Builder::getInstance()->getSettings();
        $FIELD_CONFIGS = $settings->FIELD_CONFIGS ?? [];

        if (empty($FIELD_CONFIGS)) {
            $this->stdout("No hay campos para eliminar." . PHP_EOL);
        } else {
            $this->stdout("Eliminando campos..." . PHP_EOL);
            $this->deleteFields($FIELD_CONFIGS);
        }
    }

    /**
     * Create fields based on $fieldConfigs.
     * @param array $fieldConfigs
     * @return int
     * @throws InvalidConfigException
     * @throws \Throwable
     */
    protected function createFields(array $fieldConfigs): int
    {
        $fields = Craft::$app->getField();
        foreach ($fieldConfigs as $fieldConfig) {
            $handle = fieldConfig["handle"];
            if ($fields->getFieldByHandle($handle)) {
                Console::outputWarning("Field $handle already exists.");
                continue;
            }

            // Create & save each field
            $field = Craft::createObject(array_merge($fieldConfig, []));
            if ($fields->saveField($field)) {
                Console::output("Field {$field->name} created successfully.");
                return ExitCode::OK;
            } else {
                Console::outputError(
                    "Failed to create field {$field->name}: " .
                        implode(", ", $field->getErrorSummary(true)),
                );
                return ExitCode::UNSPECIFIED_ERROR;
            }
        }
    }

    /**
     * Create fields based on $fieldConfigs.
     * @param array $fieldConfigs
     * @return int
     * @throws \Throwable
     */
    protected function deleteFields(array $fieldConfigs): int
    {
        $fields = Craft::$app->getFields();
        foreach ($fieldConfigs as $fieldConfig) {
            $handle = $fieldConfig["handle"];

            // get and delte each field
            $field = $fields->getFieldByHandle($handle);
            if ($field) {
                if (Craft::$app->getFields()->deleteField($field)) {
                    Console::output("Field $handle deleted successfully.");
                    return ExitCode::OK;
                } else {
                    Console::outputError(
                        "Failed to delete field $handle: " .
                            implode(", ", $field->getErrorSummary(true)),
                    );
                    return ExitCode::UNSPECIFIED_ERROR;
                }
            } else {
                Console::outputWarning("Field $handle does not exist.");
                continue;
            }
        }
    }

    /**
     * Create EntryTypes based on $entryTypeConfigs
     *
     * @param array $entryTypeConfigs
     * @return void
     * @throws EntryTypeNotFoundException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    protected function createEntryTypes(array $entryTypeConfigs): void
    {
        $entries = Craft::$app->getEntries();
        foreach ($entryTypeConfigs as $entryTypeConfig) {
            $handle = $entryTypeConfig["handle"];
            if ($entries->getEntryTypeByHandle($handle)) {
                Console::outputWarning("EntryType $handle already exists");
                continue;
            }
            // We use the custom field handles later on, to add them to the EntryType's layout
            unset($entryTypeConfig["customFields"]);
            // Create & save each EntryType
            $entryType = Craft::createObject(array_merge($entryTypeConfig, []));
            if (!$entries->saveEntryType($entryType)) {
                $entryType->validate();
                Console::outputWarning(
                    "EntryType $handle could not be saved" .
                        PHP_EOL .
                        print_r($entryType->getErrors(), true),
                );
                return;
            }
        }
    }
}
