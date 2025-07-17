<?php
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Database.php';

// Protect this page but allow API access
Auth::protectWebPage();

$success = false;
$error = null;
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['migrate'])) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Read the migration SQL
        $migrationSql = file_get_contents(__DIR__ . '/sql/add_folder_column.sql');
        
        // Split into individual statements (remove comments and empty lines)
        $statements = array_filter(
            array_map('trim', 
                preg_split('/;[\r\n]+/', $migrationSql)
            ), 
            function($stmt) {
                return !empty($stmt) && !preg_match('/^--/', $stmt);
            }
        );
        
        $conn->begin_transaction();
        
        foreach ($statements as $statement) {
            if (trim($statement)) {
                $messages[] = "Executing: " . substr($statement, 0, 100) . "...";
                $result = $conn->query($statement);
                if (!$result) {
                    throw new Exception("Error executing statement: " . $conn->error);
                }
                $messages[] = "✓ Success";
            }
        }
        
        $conn->commit();
        $success = true;
        $messages[] = "🎉 Database migration completed successfully!";
        
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
        }
        $error = $e->getMessage();
        $messages[] = "❌ Migration failed: " . $error;
    }
}

// Check current table structure
$tableInfo = [];
try {
    $db = new Database();
    $conn = $db->getConnection();
    $result = $conn->query("DESCRIBE captions");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $tableInfo[] = $row;
        }
    }
} catch (Exception $e) {
    $tableInfo = ['error' => $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration - Timeline</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        button:disabled { background: #6c757d; cursor: not-allowed; }
        .message { margin: 5px 0; padding: 5px; background: #f8f9fa; border-left: 3px solid #007bff; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .nav { margin: 20px 0; }
        .nav a { color: #007bff; text-decoration: none; margin-right: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Database Migration</h1>
        <p>Add folder column support for multi-folder captions</p>
        
        <div class="nav">
            <a href="index.php">← Back to Timeline Hub</a> |
            <a href="test_folders.php">🧪 Test Folders</a> |
            <a href="test_api.php">🧪 Test API</a>
        </div>
        
        <?php if ($success): ?>
            <div class="section success">
                <h3>✅ Migration Successful</h3>
                <p>Database has been updated to support multi-folder captions!</p>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="section error">
                <h3>❌ Migration Error</h3>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($messages)): ?>
            <div class="section info">
                <h3>📋 Migration Log</h3>
                <?php foreach ($messages as $message): ?>
                    <div class="message"><?php echo htmlspecialchars($message); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="section">
            <h3>📊 Current Table Structure</h3>
            <?php if (isset($tableInfo['error'])): ?>
                <p class="error">Error: <?php echo htmlspecialchars($tableInfo['error']); ?></p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Null</th>
                            <th>Key</th>
                            <th>Default</th>
                            <th>Extra</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tableInfo as $column): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($column['Field']); ?></td>
                                <td><?php echo htmlspecialchars($column['Type']); ?></td>
                                <td><?php echo htmlspecialchars($column['Null']); ?></td>
                                <td><?php echo htmlspecialchars($column['Key']); ?></td>
                                <td><?php echo htmlspecialchars($column['Default'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($column['Extra']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h3>🚀 Run Migration</h3>
            
            <?php 
            $needsMigration = true;
            foreach ($tableInfo as $column) {
                if ($column['Field'] === 'folder_name') {
                    $needsMigration = false;
                    break;
                }
            }
            ?>
            
            <?php if ($needsMigration): ?>
                <div class="warning">
                    <p><strong>⚠️ Migration Required</strong></p>
                    <p>The captions table needs to be updated to support multi-folder functionality.</p>
                    <p>This will add a <code>folder_name</code> column and update indexes.</p>
                </div>
                
                <form method="POST">
                    <button type="submit" name="migrate" onclick="return confirm('Are you sure you want to run the database migration?')">
                        🔧 Run Migration
                    </button>
                </form>
            <?php else: ?>
                <div class="success">
                    <p><strong>✅ Migration Complete</strong></p>
                    <p>The database already has multi-folder support.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h3>📝 Migration Details</h3>
            <p>This migration will:</p>
            <ul>
                <li>Add <code>folder_name</code> column to store which folder each caption belongs to</li>
                <li>Set default folder to 'driveway' for backward compatibility</li>
                <li>Create composite index on (folder_name, timestamp) for better performance</li>
                <li>Add unique constraint to prevent duplicate captions per folder+timestamp</li>
                <li>Update existing records to use 'driveway' folder</li>
            </ul>
            
            <h4>SQL Preview:</h4>
            <pre><?php echo htmlspecialchars(file_get_contents(__DIR__ . '/sql/add_folder_column.sql')); ?></pre>
        </div>
    </div>
</body>
</html>