<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('tests');

$config = new PhpCsFixer\Config();

$config
    ->setCacheFile(__DIR__ . '/build/cache/php-cs-fixer/.php-cs-fixer.cache')
    ->setRules(['@Symfony' => true])
    ->setFinder($finder);

return $config;
