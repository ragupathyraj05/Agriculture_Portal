<?php
$hash = password_hash('Demo@123', PASSWORD_DEFAULT);
file_put_contents(__DIR__ . '/hash.txt', $hash);
echo "Hash: $hash\n";
echo "Verify: " . (password_verify('Demo@123', $hash) ? 'PASS' : 'FAIL') . "\n";
