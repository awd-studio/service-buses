<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('tests');

$config = new PhpCsFixer\Config();

$config->setCacheFile(__DIR__ . '/tools/cache/.php-cs-fixer.cache');

$config->setRules([
    '@Symfony' => true,
])
    ->setFinder($finder);

return $config;