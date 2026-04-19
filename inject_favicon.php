<?php
    function injectFavicon($dir) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir)
        );
        $faviconLink = "\n    <link rel=\"icon\" type=\"image/png\" href=\"{{ asset('medflow-logo.png') }}\">\n</head>";
        foreach ($files as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['php'])) {
                $content = file_get_contents($file->getPathname());
                if (str_contains($content, '</head>') && !str_contains($content, 'medflow-logo.png">')) {
                    $content = str_replace('</head>', $faviconLink, $content);
                    file_put_contents($file->getPathname(), $content);
                    echo "Injected favicon into: " . $file->getPathname() . "\n";
                }
            }
        }
    }

    $directories = [
        __DIR__ . '/resources/views',
    ];

    foreach ($directories as $dir) {
        injectFavicon($dir);
    }
