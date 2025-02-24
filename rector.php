<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/core', // Update this to your project directory
    ]);

    // Apply upgrade rules from PHP 7.4 to 8.2
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
    ]);
};
