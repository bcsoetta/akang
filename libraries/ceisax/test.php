<?php 

echo password_hash('123456', PASSWORD_DEFAULT);

if (!password_verify('123456', '$2y$10$ujWk0FiujVrCERo4b8/O8eGwp8VHRhIWjFLtH7jXItqQwQGnPWfgC')) {
    echo "string";
}