<?php

require_once __DIR__ . '/vendor/autoload.php';

use Smalot\PdfParser\Parser;

try {
    // Initialize parser
    $parser = new Parser();
    
    // Parse PDF file
    $pdfPath = __DIR__ . '/docs/Final-Concluding-Activity-1.pdf';
    
    if (!file_exists($pdfPath)) {
        die("Error: PDF file not found at: $pdfPath\n");
    }
    
    echo "Reading PDF: $pdfPath\n";
    echo str_repeat("=", 80) . "\n\n";
    
    $pdf = $parser->parseFile($pdfPath);
    
    // Get metadata
    $details = $pdf->getDetails();
    
    echo "PDF METADATA:\n";
    echo str_repeat("-", 80) . "\n";
    foreach ($details as $key => $value) {
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        echo "$key: $value\n";
    }
    echo "\n";
    
    // Get number of pages
    $pages = $pdf->getPages();
    echo "Total Pages: " . count($pages) . "\n\n";
    
    echo str_repeat("=", 80) . "\n";
    echo "FULL TEXT CONTENT:\n";
    echo str_repeat("=", 80) . "\n\n";
    
    // Extract all text
    $text = $pdf->getText();
    echo $text;
    
    echo "\n\n" . str_repeat("=", 80) . "\n";
    echo "PAGE-BY-PAGE CONTENT:\n";
    echo str_repeat("=", 80) . "\n\n";
    
    // Extract text page by page
    foreach ($pages as $pageNumber => $page) {
        echo "--- PAGE " . ($pageNumber + 1) . " ---\n";
        echo $page->getText();
        echo "\n\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
