<?php
    function replaceInDir($dir, $search, $replace) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir)
        );
        foreach ($files as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['php'])) {
                $content = file_get_contents($file->getPathname());
                if (str_contains($content, $search)) {
                    $content = str_replace($search, $replace, $content);
                    file_put_contents($file->getPathname(), $content);
                    echo "Updated: " . $file->getPathname() . "\n";
                }
            }
        }
    }

    $directories = [
        __DIR__ . '/resources/views',
        __DIR__ . '/app/Http/Controllers',
    ];

    foreach ($directories as $dir) {
        replaceInDir($dir, 'HealthCare Plus', 'MedFlow');
        replaceInDir($dir, 'HealthCare', 'MedFlow'); // Sometimes just HealthCare is used
    }
