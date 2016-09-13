<?PHP

require_once 'wp-emember-auth.php';

function doit() {
  $str = wpea_prepare_eval_file('wpea-eval-test-file.php');
  eval($str);
  echo 'FOO: ' . FOO . ", bar: $bar\n";
  echo \wpea\foo('foo') . "\n";
}

doit();

echo 'Global FOO: ' . FOO . "\n";

if (isset($bar)) {
  echo "Global bar: $bar\n";
} else {
  echo "No global value for bar.\n";
}
