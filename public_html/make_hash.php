<?php
// make_hash.php
// Временная страница для генерации хешей

echo "<h3>Hashes:</h3>";

echo "admin123 → ";
echo password_hash("admin123", PASSWORD_DEFAULT);

echo "<br><br>";

echo "client123 → ";
echo password_hash("client123", PASSWORD_DEFAULT);