<?php
    function replaceLogo($dir) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir)
        );
        foreach ($files as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['php'])) {
                $content = file_get_contents($file->getPathname());
                
                // Replace old plain text titles or <i class="bi bi-heart-pulse-fill text-danger"></i><br>MedFlow<br><span class="text-info">Plus</span>
                // with an <img src="{{ asset('medflow-logo.png') }}">
                $pattern = '/<h1[^>]*>.*?MedFlow.*?<\/h1>/si';
                
                // Instead of a dangerous regex, let's just do targeted replaces.
                $replacements = [
                    '<i class="bi bi-heart-pulse-fill text-danger"></i><br>MedFlow<br><span class="text-info">Plus</span>' => '<img src="{{ asset(\'medflow-logo.png\') }}" alt="MedFlow Logo" style="height: 60px; max-width: 100%; object-fit: contain;">',
                    '<i class="bi bi-heart-pulse"></i> {{ config(\'app.name\', \'MedFlow\') }}' => '<img src="{{ asset(\'medflow-logo.png\') }}" alt="MedFlow Logo" style="height: 40px; margin-top: -8px;">',
                    '<i class="bi bi-heart-pulse text-danger"></i> MedFlow' => '<img src="{{ asset(\'medflow-logo.png\') }}" alt="MedFlow Logo" style="height: 60px;">',
                ];

                foreach($replacements as $search => $replace) {
                    if (str_contains($content, $search)) {
                        $content = str_replace($search, $replace, $content);
                    }
                }
                
                file_put_contents($file->getPathname(), $content);
            }
        }
    }

    $directories = [
        __DIR__ . '/resources/views',
    ];

    foreach ($directories as $dir) {
        replaceLogo($dir);
    }
