<?php

$helper_files = [
    "app/Sheba/ResourceScheduler/functions.php",
    "app/Sheba/FileManagers/functions.php",
    "app/Sheba/Partner/functions.php",
    "app/Sheba/Logs/functions.php",
    "app/Sheba/Helpers/Formatters/functions.php",
    "app/Sheba/Helpers/Time/functions.php",
    "app/Sheba/Helpers/String/functions.php",
    "app/Sheba/Helpers/Model/functions.php",
    "app/Sheba/Helpers/Http/functions.php",
    "app/Sheba/Helpers/Miscellaneous/functions.php",
    "app/Sheba/Helpers/BearerToken/token_helper_functions.php"
];

foreach ($helper_files as $file) {
    $file = dirname(dirname(__DIR__)) . "/" . $file;
    if (file_exists($file))
        require $file;
}
