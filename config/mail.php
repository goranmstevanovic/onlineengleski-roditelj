<?php

return [
    'host'       => $_ENV['MAIL_HOST'],
    'username'   => $_ENV['MAIL_USERNAME'],
    'password'   => $_ENV['MAIL_PASSWORD'],
    'port'       => (int) $_ENV['MAIL_PORT'],
    'encryption' => $_ENV['MAIL_ENCRYPTION'],
    'from_email' => $_ENV['MAIL_FROM_EMAIL'],
    'from_name'  => POSILJALAC,
];
