<?php
// Final diagnostic script to verify environment variables
header('Content-Type: text/html; charset=utf-8');

// Report all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>API Key Test - Final Check</title><style>
    body { font-family: sans-serif; margin: 20px; line-height: 1.6; max-width: 900px; margin: 40px auto; color: #333; background: #f5f7fa; }
    .card { background: #fff; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 40px; border: 1px solid #e1e4e8; }
    h1 { color: #1a202c; border-bottom: 2px solid #edf2f7; padding-bottom: 15px; margin-top: 0; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; background: #fff; }
    th, td { border: 1px solid #e2e8f0; padding: 14px 18px; text-align: left; }
    th { background: #f8fafc; color: #4a5568; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; }
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .found { background: #c6f6d5; color: #22543d; }
    .missing { background: #fed7d7; color: #822727; }
    .code { font-family: monospace; background: #edf2f7; padding: 3px 8px; border-radius: 4px; font-size: 13px; color: #2d3748; }
    .footer { margin-top: 40px; font-size: 13px; color: #a0aec0; text-align: center; }
</style></head><body>
<div class='card'>
    <h1>Environment Diagnostics</h1>";

// Check for common API keys and secrets based on your environment
$keysToCheck = [
    'REPLIT_AI_API_KEY',
    'DATABASE_URL',
    'SESSION_SECRET',
    'PGDATABASE',
    'PGHOST',
    'PGUSER'
];

echo "<table>";
echo "<tr><th>Variable Name</th><th>Status</th><th>Value Preview</th></tr>";

foreach ($keysToCheck as $key) {
    // Try multiple ways to fetch the value
    $value = getenv($key);
    if ($value === false || $value === null || $value === '') {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
    }
    
    echo "<tr>";
    echo "<td><span class='code'>$key</span></td>";
    if ($value) {
        $preview = htmlspecialchars(substr($value, 0, 4)) . "..." . htmlspecialchars(substr($value, -4));
        echo "<td><span class='status-badge found'>✅ DETECTED</span></td>";
        echo "<td><span class='code'>$preview</span></td>";
    } else {
        echo "<td><span class='status-badge missing'>❌ NOT FOUND</span></td>";
        echo "<td>-</td>";
    }
    echo "</tr>";
}
echo "</table>";

echo "<h3>PHP Info Summary</h3>";
echo "<ul>
    <li><strong>PHP Version:</strong> " . htmlspecialchars(phpversion()) . "</li>
    <li><strong>Server Software:</strong> " . htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</li>
    <li><strong>Variables Order:</strong> " . htmlspecialchars(ini_get('variables_order')) . " (Needs 'E' to populate \$_ENV)</li>
</ul>";

echo "<p>If you see 'NOT FOUND', please ensure the secret is added in the <strong>Secrets</strong> tool (lock icon) in the sidebar. After adding a secret, you must <strong>restart the application</strong> for changes to take effect.</p>
</div>
<div class='footer'>
    Final diagnostic run &bull; " . date('Y-m-d H:i:s') . "
</div>
</body></html>";
?>