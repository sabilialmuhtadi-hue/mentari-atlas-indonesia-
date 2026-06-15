<?php
$file = 'c:\\Users\\user\\Downloads\\spk-sales-order-revisi1\\spk-sales-order\\resources\\views\\layouts\\app.blade.php';
$content = file_get_contents($file);

// 1. Update CSS .sidebar (remove position: fixed, add sticky)
$content = str_replace('            position: fixed;', '            position: sticky;', $content);

// 2. Update CSS .top-header
$content = preg_replace('/\.top-header \{[^}]+\}/s', '.top-header { position: sticky; top: 0; height: 60px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid #e2e8f0; z-index: 1040; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; }', $content);

// 3. Update CSS .main-wrapper
$content = preg_replace('/\.main-wrapper \{[^}]+\}/s', '.main-wrapper { display: flex; flex-direction: column; flex-grow: 1; min-width: 0; min-height: 100vh; }', $content);

// 4. Move <header> outside of <div class="sidebar">
$headerStart = strpos($content, '<header class="top-header">');
if ($headerStart !== false) {
    $headerEnd = strpos($content, '</header>') + 9;
    $headerHtml = substr($content, $headerStart, $headerEnd - $headerStart);
    $content = substr_replace($content, '', $headerStart, $headerEnd - $headerStart); // remove header from sidebar
    
    $sidebarEnd = strpos($content, '</ul>', $headerStart);
    $sidebarClose = strpos($content, '</div>', $sidebarEnd) + 6; // closes <div class="sidebar">
    
    // insert right after sidebar closes
    $content = substr_replace($content, "\n\n    <div class=\"main-wrapper\">\n        " . $headerHtml, $sidebarClose, 0);
}

// 5. Wrap everything inside a flex container
$content = preg_replace('/<div id="top-loader"><\/div>\s*@auth/s', "<div id=\"top-loader\"></div>\n\n    <div class=\"d-flex flex-row w-100 min-vh-100\">\n    @auth", $content);

// 6. Remove the old main-wrapper starting div
$content = preg_replace('/<div class="\{\{ Auth::check\(\) \? \'main-wrapper\' : \'\' \}\}">\s*<main/s', '<main', $content);

// 7. Adjust closing tags
// Wait, if I replaced the old main-wrapper start div, I should replace its end div.
// Original end was `</main> </div>`
$content = str_replace("</main>\n    </div>", "</main>", $content);

// Add the closing for the master d-flex container right before </body>
$content = preg_replace('/<\/body>/s', "    </div>\n</body>", $content);

file_put_contents($file, $content);
echo "Refactoring completed.\n";
