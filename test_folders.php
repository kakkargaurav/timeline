<?php
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/FolderManager.php';
require_once __DIR__ . '/classes/TimelineManager.php';

// Protect this page but allow API access
Auth::protectWebPage();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folder Test - Timeline</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .folder { background: #f8f9fa; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .timeline { background: #e9ecef; padding: 10px; margin: 10px 0; border-radius: 4px; }
        pre { background: #f1f3f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Multi-Folder Timeline Test</h1>
        
        <div class="test-section">
            <h2>📁 Folder Manager Test</h2>
            <?php
            $folderManager = new FolderManager();
            $folders = $folderManager->getImageFolders();
            ?>
            <p><strong>Found <?php echo count($folders); ?> image folders:</strong></p>
            
            <?php foreach ($folders as $folder): ?>
                <div class="folder">
                    <h3><?php echo htmlspecialchars($folder['display_name']); ?> (<?php echo htmlspecialchars($folder['name']); ?>)</h3>
                    <ul>
                        <li><strong>Path:</strong> <?php echo htmlspecialchars($folder['path']); ?></li>
                        <li><strong>Image Count:</strong> <?php echo $folder['image_count']; ?></li>
                        <li><strong>Total Size:</strong> <?php echo $folderManager->formatFileSize($folder['total_size']); ?></li>
                        <?php if ($folder['latest_image']): ?>
                            <li><strong>Latest Image:</strong> <?php echo htmlspecialchars($folder['latest_image']['filename']); ?></li>
                        <?php endif; ?>
                    </ul>
                    
                    <?php if ($folder['folder_stats']): ?>
                        <p><strong>Statistics:</strong></p>
                        <pre><?php echo htmlspecialchars(json_encode($folder['folder_stats'], JSON_PRETTY_PRINT)); ?></pre>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="test-section">
            <h2>📸 Timeline Manager Test</h2>
            
            <?php foreach ($folders as $folder): ?>
                <div class="timeline">
                    <h3>Timeline for <?php echo htmlspecialchars($folder['display_name']); ?></h3>
                    <?php
                    try {
                        $timeline = new TimelineManager($folder['path']);
                        $images = $timeline->scanImages();
                        $timelineData = $timeline->getTimelineData();
                        ?>
                        <p><strong>Images found:</strong> <?php echo count($images); ?></p>
                        <p><strong>Timeline items:</strong> <?php echo count($timelineData); ?></p>
                        
                        <?php if (!empty($images)): ?>
                            <p><strong>Sample images:</strong></p>
                            <ul>
                                <?php foreach (array_slice($images, 0, 3) as $image): ?>
                                    <li><?php echo htmlspecialchars($image['filename']); ?> - <?php echo htmlspecialchars($image['timestamp']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        
                    <?php } catch (Exception $e) { ?>
                        <p style="color: red;"><strong>Error:</strong> <?php echo htmlspecialchars($e->getMessage()); ?></p>
                    <?php } ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="test-section">
            <h2>🔗 Test Links</h2>
            <p>Test the timeline for each folder:</p>
            <ul>
                <?php foreach ($folders as $folder): ?>
                    <li><a href="timeline.php?folder=<?php echo urlencode($folder['name']); ?>" target="_blank">
                        <?php echo htmlspecialchars($folder['display_name']); ?> Timeline
                    </a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="test-section">
            <h2>🔙 Navigation</h2>
            <p>
                <a href="index.php">← Back to Timeline Hub</a> |
                <a href="test_api.php">🧪 API Tests</a>
            </p>
        </div>
    </div>
</body>
</html>