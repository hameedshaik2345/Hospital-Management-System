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
            
            // Fix broken CSS snippet left over from my previous regex
            $broken1 = "
            font-family: 'Poppins', sans-serif;
        }";
            $broken2 = "            font-family: 'Poppins', sans-serif;\n        }";
            $broken3 = "font-family: 'Poppins', sans-serif;\n        }";

            // In admin.blade.php, doctor.blade.php, they had slightly different body blocks. Let's just fix universally.
            // Using a simple regex to wipe out the dangling properties before '.dashboard-layout' or similar.
            
            // Let's just do a clean regex repair. If there's an orphaned `}` right before `.dashboard-layout` or `.auth-bg` that is preceded by `font-family`, wipe it.
            $content = preg_replace("/^\s*font-family:[^;]+;\s*\}/m", "", $content);

            file_put_contents($file, $content);
        }
    }
    echo "Done resolving CSS syntax errors.";
