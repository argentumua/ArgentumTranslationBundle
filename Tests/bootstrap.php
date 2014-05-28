<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

if (!is_file($loaderFile = __DIR__.'/../vendor/autoload.php')) {
    throw new \RuntimeException('Could not find autoload.php in vendor/. Did you run "composer install --dev"?');
}

$loader = require $loaderFile;

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

initDatabase();

function initDatabase()
{
    $sqls = array(
        "CREATE TABLE translation_domain (id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, createdAt DATETIME DEFAULT NULL, updatedAt DATETIME DEFAULT NULL, PRIMARY KEY(id))",
        "CREATE TABLE translation_domain_translation (id INTEGER NOT NULL, translatable_id INTEGER DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_F6B1F942C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES translation_domain (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)",
        "CREATE TABLE translation_unit (id INTEGER NOT NULL, domain_id INTEGER NOT NULL, token VARCHAR(255) NOT NULL, createdAt DATETIME DEFAULT NULL, updatedAt DATETIME DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_F199AFC8115F0EE5 FOREIGN KEY (domain_id) REFERENCES translation_domain (id) NOT DEFERRABLE INITIALLY IMMEDIATE)",
        "CREATE TABLE translation_unit_translation (id INTEGER NOT NULL, translatable_id INTEGER DEFAULT NULL, content CLOB DEFAULT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_62CA630B2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES translation_unit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)",
    );

    $dbPath = sys_get_temp_dir() . '/ArgentumTranslationBundle/cache/test.db';
    if (!is_dir($dbDir = dirname($dbPath))) {
        mkdir($dbDir, 0777, true);
    }

    if (is_file($dbPath)) {
        unlink($dbPath);
    }

    if ($db = new PDO('sqlite:' . $dbPath)) {
        $db->beginTransaction();
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }
        $db->commit();
    }
}
