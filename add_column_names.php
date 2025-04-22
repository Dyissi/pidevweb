<?php

$directory = 'd:/symfonyProjects/pidev/src/Entity'; // Path to your entity directory

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $filePath = $file->getPathname();
        $content = file_get_contents($filePath);

        // Match all #[ORM\Column] annotations without a "name" attribute
        $content = preg_replace_callback(
            '/#\[ORM\\\Column\((?!.*name=)(.*?)\)\]/',
            function ($matches) {
                $attributes = $matches[1];

                // Extract the variable name from the next line
                if (preg_match('/private\s+\??\w+\s+\$(\w+);/', $matches[0], $varMatch)) {
                    $columnName = $varMatch[1];
                    return "#[ORM\\Column(name: \"$columnName\", $attributes)]";
                }

                return $matches[0];
            },
            $content
        );

        // Save the modified content back to the file
        file_put_contents($filePath, $content);
        echo "Processed: $filePath\n";
    }
}