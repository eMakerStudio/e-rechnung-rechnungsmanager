<?php
// Fügen Sie diese Zeilen am Anfang Ihres Skripts hinzu
echo "OpenSSL-Status: ";
var_dump(extension_loaded('openssl'));
echo "\nOpenSSL-Version: " . OPENSSL_VERSION_TEXT;
phpinfo(INFO_MODULES); // Zeigt Details zu installierten Modulen
?>