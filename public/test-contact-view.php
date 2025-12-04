<?php
// Test if contact-submissions view has delete button
header('Content-Type: text/plain');

$file = __DIR__ . '/../app/views/admin/contact-submissions.php';

echo "=== CONTACT SUBMISSIONS VIEW TEST ===\n\n";

if (file_exists($file)) {
    echo "✓ File exists: YES\n";
    echo "File path: $file\n\n";

    $content = file_get_contents($file);

    // Check for delete button
    if (strpos($content, 'Delete') !== false && strpos($content, '/delete') !== false) {
        echo "✓ Delete button code: FOUND\n\n";

        // Show the delete button section
        echo "Delete button section:\n";
        echo "=====================\n";

        // Extract lines around the delete button
        $lines = explode("\n", $content);
        foreach ($lines as $num => $line) {
            if (stripos($line, 'Delete Button') !== false) {
                // Show 10 lines around the delete button
                for ($i = max(0, $num - 2); $i < min(count($lines), $num + 10); $i++) {
                    echo ($i + 1) . ": " . $lines[$i] . "\n";
                }
                break;
            }
        }
    } else {
        echo "✗ Delete button code: NOT FOUND\n";
        echo "\nThis means the old version is still on the server!\n";
    }

    echo "\n\nFile last modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
    echo "File size: " . filesize($file) . " bytes\n";

} else {
    echo "✗ File exists: NO\n";
    echo "Expected location: $file\n";
}

echo "\n=== END TEST ===\n";
