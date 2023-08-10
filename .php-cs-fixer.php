<?php

if (!file_exists(__DIR__ . '/src')) {
    exit(0);
}

$config = new PhpCsFixer\Config();
$finder = new PhpCsFixer\Finder();

$finder
    ->in(__DIR__ . '/src')
    ->append([
        __DIR__ . '/public',
        __DIR__ . '/.php-cs-fixer.php',
    ])
    ->exclude('tests');

$config
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP71Migration' => true,
        '@PHPUnit75Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'protected_to_private' => false,
        'native_constant_invocation' => ['strict' => false],
        'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => false],
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
        'modernize_strpos' => true,
        'get_class_to_class_keyword' => true,
        'final_class' => true,
        'concat_space' => ['spacing' => 'one'],
        'phpdoc_to_comment' => ['ignored_tags' => ['psalm-suppress']],
    ])
    ->setCacheFile(__DIR__ . '/tools/cache/php-cs-fixer/.php-cs-fixer.cache');

return $config;
