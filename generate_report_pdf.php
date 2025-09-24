<?php
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Read the markdown content
$markdownContent = file_get_contents('healthcare_system_analysis_report.md');

// Convert markdown to HTML (basic conversion)
$htmlContent = convertMarkdownToHtml($markdownContent);

// Create PDF
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);

// Add CSS styling for better PDF appearance
$styledHtml = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #2c3e50;
            font-size: 24px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        h2 {
            color: #34495e;
            font-size: 18px;
            margin-top: 25px;
            margin-bottom: 15px;
            border-left: 4px solid #3498db;
            padding-left: 10px;
        }

        h3 {
            color: #2c3e50;
            font-size: 14px;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        h4 {
            color: #34495e;
            font-size: 12px;
            margin-top: 15px;
            margin-bottom: 8px;
        }

        p {
            margin-bottom: 10px;
            text-align: justify;
        }

        ul, ol {
            margin-bottom: 15px;
            padding-left: 20px;
        }

        li {
            margin-bottom: 5px;
        }

        .status-complete {
            color: #27ae60;
            font-weight: bold;
        }

        .status-incomplete {
            color: #e74c3c;
            font-weight: bold;
        }

        .status-warning {
            color: #f39c12;
            font-weight: bold;
        }

        .module-header {
            background-color: #ecf0f1;
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }

        .priority-high {
            background-color: #ffe6e6;
            padding: 10px;
            border-left: 4px solid #e74c3c;
            margin: 10px 0;
        }

        .priority-medium {
            background-color: #fff3cd;
            padding: 10px;
            border-left: 4px solid #f39c12;
            margin: 10px 0;
        }

        .priority-low {
            background-color: #e6f3ff;
            padding: 10px;
            border-left: 4px solid #3498db;
            margin: 10px 0;
        }

        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .table-container {
            margin: 15px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            text-align: center;
            color: #666;
            font-size: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        code {
            background-color: #f4f4f4;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 10px;
        }

        blockquote {
            border-left: 4px solid #ccc;
            padding-left: 15px;
            margin-left: 0;
            font-style: italic;
            color: #666;
        }

        .highlight {
            background-color: #ffffcc;
            padding: 2px 4px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    $htmlContent

    <div class='footer'>
        <p>Healthcare Management System Analysis Report - Generated on " . date('F j, Y g:i A') . "</p>
        <p>System Analysis Completion: 100% | Overall System Readiness: 90% Complete</p>
    </div>
</body>
</html>";

$dompdf->loadHtml($styledHtml);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output the PDF
$filename = 'Healthcare_System_Analysis_Report_' . date('Y-m-d_H-i-s') . '.pdf';
$dompdf->stream($filename, array('Attachment' => true));

function convertMarkdownToHtml($markdown) {
    // Basic markdown to HTML conversion
    $html = $markdown;

    // Replace headers
    $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^#### (.+)$/m', '<h4>$1</h4>', $html);

    // Replace status indicators
    $html = str_replace('✅ COMPLETE', '<span class="status-complete">✅ COMPLETE</span>', $html);
    $html = str_replace('⚠️ NEEDS IMPLEMENTATION', '<span class="status-incomplete">⚠️ NEEDS IMPLEMENTATION</span>', $html);
    $html = str_replace('✅', '<span class="status-complete">✅</span>', $html);
    $html = str_replace('⚠️', '<span class="status-warning">⚠️</span>', $html);
    $html = str_replace('❌', '<span class="status-incomplete">❌</span>', $html);

    // Replace bold text
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);

    // Replace italic text
    $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);

    // Replace code blocks
    $html = preg_replace('/```(.+?)```/s', '<pre><code>$1</code></pre>', $html);
    $html = preg_replace('/`(.+?)`/', '<code>$1</code>', $html);

    // Replace unordered lists
    $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.+<\/li>)/s', '<ul>$1</ul>', $html);

    // Replace numbered lists
    $html = preg_replace('/^\d+\. (.+)$/m', '<li>$1</li>', $html);

    // Replace line breaks
    $html = preg_replace('/\n\n/', '</p><p>', $html);
    $html = '<p>' . $html . '</p>';

    // Clean up empty paragraphs
    $html = preg_replace('/<p><\/p>/', '', $html);
    $html = preg_replace('/<p>(<h[1-6]>.+<\/h[1-6]>)<\/p>/', '$1', $html);
    $html = preg_replace('/<p>(<ul>.+<\/ul>)<\/p>/s', '$1', $html);
    $html = preg_replace('/<p>(<ol>.+<\/ol>)<\/p>/s', '$1', $html);

    // Add module styling
    $html = preg_replace('/<h3>(\d+\. .+ ✅ COMPLETE)<\/h3>/', '<div class="module-header"><h3>$1</h3></div>', $html);
    $html = preg_replace('/<h3>(\d+\. .+ ⚠️ NEEDS IMPLEMENTATION)<\/h3>/', '<div class="module-header"><h3>$1</h3></div>', $html);

    // Add priority styling
    $html = str_replace('<h4>High Priority:', '<div class="priority-high"><h4>High Priority:', $html);
    $html = str_replace('<h4>Medium Priority:', '<div class="priority-medium"><h4>Medium Priority:', $html);
    $html = str_replace('<h4>Low Priority:', '<div class="priority-low"><h4>Low Priority:', $html);

    // Add page breaks for major sections
    $html = str_replace('<h2>Module Analysis</h2>', '<div class="page-break"></div><h2>Module Analysis</h2>', $html);
    $html = str_replace('<h2>Recommendations</h2>', '<div class="page-break"></div><h2>Recommendations</h2>', $html);
    $html = str_replace('<h2>Technical Specifications</h2>', '<div class="page-break"></div><h2>Technical Specifications</h2>', $html);

    return $html;
}

?>