<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setUsingCache(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHPUnit75Migration:risky' => true,
        'php_unit_dedicate_assert' => ['target' => '5.6'],
        'array_syntax' => ['syntax' => 'short'],
        'fopen_flags' => false,
        'protected_to_private' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        Finder::create()
            ->exclude('LopendeZakenBundle/fixtures')
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/tests')
    );
