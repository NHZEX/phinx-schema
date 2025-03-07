<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        'src',
        'tests',
    ]);
$config = new PhpCsFixer\Config();

return $config
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PER-CS1.0'                 => true,
        '@PER-CS1.0:risky'           => true,
        '@PHP74Migration'            => true,
        '@PHP74Migration:risky'      => true,
        // '@Symfony'                   => true,
        // '@Symfony:risky'             => true,
        // global_namespace_import.import_functions 之后找机会重新改为 null
        // 'global_namespace_import'    => ['import_classes' => true, 'import_constants' => null, 'import_functions' => null],
        // 'operator_linebreak'         => ['only_booleans' => true, 'position' => 'beginning'],
        // 'no_unneeded_final_method'   => false,
        // risky
        'native_function_invocation' => ['include' => ['@compiler_optimized'], 'scope' => 'namespaced', 'strict' => true],
        'function_to_constant'       => true,
        // 暂时不要发生过大的变动范围
        'blank_line_between_import_groups' => false,
        'declare_strict_types' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
