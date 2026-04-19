<?php
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/resources/views')
    );
    $newLogo = '<img src="{{ asset(\'medflow-logo.png\') }}" alt="MedFlow Logo" style="height: 140px; margin: -40px 0; max-width: 100%; object-fit: contain; mix-blend-mode: multiply; transform: scale(1.6);">';

    foreach ($files as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php'])) {
            $content = file_get_contents($file->getPathname());
            
            $changed = false;
            
            $searches = [
                '💙 MedFlow',
                '🟢 MedFlow',
                '💊 MedFlow',
                '<i class="bi bi-heart-pulse-fill text-danger"></i> MedFlow',
            ];

            foreach($searches as $search) {
                if (str_contains($content, $search)) {
                    $content = str_replace($search, $newLogo, $content);
                    $changed = true;
                }
            }
            
            if ($changed) {
                file_put_contents($file->getPathname(), $content);
                echo "Added giant logo in: " . $file->getPathname() . "\n";
            }
        }
    }
