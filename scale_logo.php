<?php
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/resources/views')
    );
    foreach ($files as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php'])) {
            $content = file_get_contents($file->getPathname());
            
            $old1 = 'style="height: 60px; max-width: 100%; object-fit: contain;"';
            $old2 = 'style="height: 60px;"';
            $old3 = 'style="height: 40px; margin-top: -8px;"';

            $newScale = 'style="height: 140px; margin: -40px 0; max-width: 100%; object-fit: contain; mix-blend-mode: multiply; transform: scale(1.6);"';
            
            $changed = false;
            if (str_contains($content, $old1)) {
                $content = str_replace($old1, $newScale, $content);
                $changed = true;
            }
            if (str_contains($content, $old2)) {
                $content = str_replace($old2, $newScale, $content);
                $changed = true;
            }
            if (str_contains($content, $old3)) {
                // for navbar, less scale
                $content = str_replace($old3, 'style="height: 80px; margin: -30px 0; object-fit: contain; mix-blend-mode: multiply; transform: scale(1.8);"', $content);
                $changed = true;
            }

            if ($changed) {
                file_put_contents($file->getPathname(), $content);
                echo "Scaled logo in: " . $file->getPathname() . "\n";
            }
        }
    }
