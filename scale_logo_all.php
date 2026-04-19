<?php
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/resources/views')
    );
    foreach ($files as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php'])) {
            $content = file_get_contents($file->getPathname());
            
            $changed = false;
            // Matches any img with medflow-logo that doesn't have the new style applied yet.
            if (str_contains($content, 'medflow-logo') && str_contains($content, 'style="height: 60px; max-width: 100%; object-fit: contain;"')) {
                $content = str_replace('style="height: 60px; max-width: 100%; object-fit: contain;"', 'style="height: 140px; margin: -40px 0; max-width: 100%; object-fit: contain; mix-blend-mode: multiply; transform: scale(1.6);"', $content);
                $changed = true;
            }
            if (str_contains($content, 'medflow-logo') && str_contains($content, 'style="height: 60px;"')) {
                $content = str_replace('style="height: 60px;"', 'style="height: 140px; margin: -40px 0; max-width: 100%; object-fit: contain; mix-blend-mode: multiply; transform: scale(1.6);"', $content);
                $changed = true;
            }
            
            if ($changed) {
                file_put_contents($file->getPathname(), $content);
                echo "Scaled logo in: " . $file->getPathname() . "\n";
            }
        }
    }
