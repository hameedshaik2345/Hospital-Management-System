<?php
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/resources/views')
    );
    foreach ($files as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php'])) {
            $content = file_get_contents($file->getPathname());
            
            $old1 = '<link rel="icon" type="image/png" href="{{ asset(\'medflow-logo.png\') }}">';
            $new1 = '<link rel="icon" type="image/svg+xml" href="{{ asset(\'medflow-favicon.svg\') }}">';

            if (str_contains($content, $old1)) {
                $content = str_replace($old1, $new1, $content);
                file_put_contents($file->getPathname(), $content);
                echo "Updated favicon in: " . $file->getPathname() . "\n";
            }
        }
    }
