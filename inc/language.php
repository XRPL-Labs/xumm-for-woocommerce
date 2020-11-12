<?php
$dir = dirname(plugin_dir_path(__FILE__));

$loc = get_locale();
$loc =  str_replace('_formal', '', $loc);

$language = $dir . '/languages/xumm-payment.'.$loc.'.json';
if(file_exists($language)) {
    $lang = json_decode(file_get_contents($language));
} else {
    $lang = json_decode(file_get_contents($dir . '/languages/xumm-payment.en_EN.json'));
}

?>