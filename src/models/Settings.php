<?php

namespace anuarhdz\builder\models;

use Craft;
use craft\base\Model;

/**
 * Builder settings
 */
class Settings extends Model
{
    public array $FIELD_CONFIGS = [];
    public array $ENTRY_TYPE_CONFIGS = [];
    public array $SECTION_CONFIGS = [];

    public function defineRules(): array
    {
        return [[["FIELD_CONFIGS", "ENTRY_TYPE_CONFIGS", "SECTION_CONFIGS"], "array"]];
    }
}
