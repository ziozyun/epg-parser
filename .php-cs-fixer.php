<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
  ->in(__DIR__ . '/src')
  ->in(__DIR__ . '/bin')
  ->in(__DIR__ . '/config');

return (new PhpCsFixer\Config())
  ->setFinder($finder)
  ->setRiskyAllowed(false)
  ->setIndent('  ')
  ->setLineEnding("\n")
  ->setRules([
    '@PSR12' => true,
    'array_indentation' => true,
    'array_syntax' => ['syntax' => 'short'],
    'binary_operator_spaces' => ['default' => 'single_space'],
    'blank_line_after_opening_tag' => true,
    'cast_spaces' => ['space' => 'single'],
    'concat_space' => ['spacing' => 'one'],
    'declare_equal_normalize' => ['space' => 'single'],
    'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
    'no_extra_blank_lines' => true,
    'no_trailing_whitespace' => true,
    'no_trailing_whitespace_in_comment' => true,
    'no_whitespace_in_blank_line' => true,
    'phpdoc_trim' => true,
    'single_blank_line_at_eof' => true,
    'single_line_after_imports' => true,
    'single_quote' => true,
    'ternary_operator_spaces' => true,
    'trim_array_spaces' => true,
  ]);
