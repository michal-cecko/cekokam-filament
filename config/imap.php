<?php

return [
    'username' => env('IMAP_USERNAME'),
    'password' => env('IMAP_PASSWORD'),
    'hostname' => env('IMAP_HOST', '{imap.m1.websupport.sk:993/imap/ssl/novalidate-cert}INBOX'),
];
