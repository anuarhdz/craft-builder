<?php

namespace anuarhdz\builder;

use Craft;
use anuarhdz\builder\models\Settings;
use craft\base\Model;
use craft\base\Plugin;

/**
 * Builder plugin
 *
 * @method static Builder getInstance()
 * @method Settings getSettings()
 * @author Anuar Reyes <anuar@hey.com>
 * @copyright Anuar Reyes
 * @license MIT
 */
class Builder extends Plugin
{
    public string $schemaVersion = "1.0.0";
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            "components" => [
                // Define component configs here...
            ],
            // "settings" => [
            //     "structureJson" => [
            //         "type" => \craft\fields\Json::class,
            //     ],
            // ],
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->attachEventHandlers();

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function () {
            // ...
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate("builder/_settings.twig", [
            "plugin" => $this,
            "settings" => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/5.x/extend/events.html to get started)
    }
}
