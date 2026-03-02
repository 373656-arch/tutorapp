<?php
// Test script to verify environment variables
header('Content-Type: text/html; charset=utf-8');

echo "<html><head><title>API Key Test</title><style>
    body { font-family: sans-serif; margin: 20px; line-height: 1.6; max-width: 900px; margin: 40px auto; color: #333; }
    .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; border: 1px solid #eee; }
    h1 { color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #edf2f7; padding: 12px 15px; text-align: left; }
    th { background: #f8fafc; color: #4a5568; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
    .found { background: #def7ec; color: #03543f; }
    .missing { background: #fde8e8; color: #9b1c1c; }
    .code { font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 14px; }
    .footer { margin-top: 30px; font-size: 13px; color: #718096; text-align: center; }
</style></head><body>
<div class='card'>
    <h1>API Key Verification</h1>";

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
    // getenv is the most reliable way to read Replit secrets in PHP
    $value = getenv($key);
    
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

echo "<h3>System Status</h3>
<p>PHP <strong>" . htmlspecialchars(phpversion()) . "</strong> is correctly reading from the environment. All secrets marked as 'Detected' are available to your application code using <code>getenv('VARIABLE_NAME')</code>.</p>
</div>
<div class='footer'>
    This test file (<code>test.php</code>) can be safely deleted once you've confirmed your configuration.
</div>
</body></html>";
?>