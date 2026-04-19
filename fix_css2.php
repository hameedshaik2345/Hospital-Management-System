<?php
    $files = [
        __DIR__ . '/resources/views/components/layouts/dashboard.blade.php',
        __DIR__ . '/resources/views/components/layouts/admin.blade.php',
        __DIR__ . '/resources/views/components/layouts/doctor.blade.php',
        __DIR__ . '/resources/views/components/layouts/pharmacist.blade.php',
        __DIR__ . '/resources/views/components/auth-layout.blade.php',
    ];

    foreach ($files as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            
            // Clean up ANY stray closing braces at the very start of the style block or after imports
            $content = preg_replace('/(<style>[\s\n]*)(?:@import[^;]+;[\s\n]*)*\K\s*\}/s', '', $content);
            
            // Or just specifically match the orphaned `}`
            $content = str_replace("<style>\n         }", "<style>", $content);
            $content = str_replace("<style>\n\n        }", "<style>", $content);

            // In dashboard.blade.php:
            // @import url('...');
            // @import url('...');
            // 
            // } 
            $content = preg_replace('/(@import[^;]+;[\s\n]*)\}/i', '$1', $content);

            file_put_contents($file, $content);
        }
    }
    echo "Done resolving CSS syntax errors completely.";
