<?php

function file_get_php_classes($filepath) {
  $php_code = file_get_contents($filepath);
  $classes = get_php_classes($php_code);
  return $classes;
}

function get_php_classes($php_code) {
  $classes = array();
  $tokens = token_get_all($php_code);
  $count = count($tokens);
  for ($i = 2; $i < $count; $i++) {
    if (   $tokens[$i - 2][0] == T_CLASS
        && $tokens[$i - 1][0] == T_WHITESPACE
        && $tokens[$i][0] == T_STRING) {

        $class_name = $tokens[$i][1];
        $classes[] = $class_name;
    }
  }
  return $classes;
}


require_once dirname(__DIR__).'/source/rb/_filelist.php';

$classes = array();
foreach($files as $file){
	$cs = file_get_php_classes(dirname(__DIR__).'/source/rb/'.$file);
	foreach($cs as $c){
		$classes[$c] = $file;
	}
}

$data = '<?php '.PHP_EOL
		.' return '.var_export($classes,true)
		.';';
if(file_put_contents(dirname(__DIR__).'/source/rb/classes.php', $data)===FALSE){
	return dirname(__DIR__).'/source/rb/classes.php';
	return 1;
}

return 0;
