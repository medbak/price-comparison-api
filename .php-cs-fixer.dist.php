<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->exclude('var')
;

$config = new PhpCsFixer\Config();

return $config->setRules([
    '@Symfony' => true,
    '@Symfony:risky' => true,
    'declare_strict_types' => true,
    'single_line_throw' => false,
    'class_definition' => [
        'multi_line_extends_each_single_line' => true,
        'inline_constructor_arguments' => false,
    ],
    'nullable_type_declaration' => true,
])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
