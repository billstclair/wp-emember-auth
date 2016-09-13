<?php
/**
 * This file is required by test-wpea-eval.php.
 * It is not intended to be used at top-level.
 */

define('FOO', 'bar');
$bar = 'foo';

function foo($x) {
  return $x . $x;
}

require_once 'wp-settings.php';
