<?php
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/FolderManager.php';

// Protect this page but allow API access
Auth::protectWebPage();

// Get all image folders
$folderManager = new FolderManager();
$folders = $folderManager->getImageFolders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline Hub - Image Folders</title>
    <link rel="stylesheet" href="css/timeline.css">
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📷</text></svg>">
    <style>
        /* Additional styles for folder grid */
        .folders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin: 40px 0;
            padding: 0 20px;
        }

        .folder-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .folder-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            text-decoration: none;
            color: inherit;
        }

        .folder-thumbnail {
            width: 100%;
            height: 200px;
            background: #f5f5f5;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .folder-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }

        .folder-thumbnail .no-image {
            color: #999;
            font-size: 3rem;
        }

        .folder-info h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .folder-stats {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding: 10px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }

        .stat-item {
            text-align: center;
            flex: 1;
        }

        .stat-number {
            display: block;
            font-size: 1.2rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
            margin-top: 2px;
        }

        .folder-meta {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.4;
        }

        .folder-meta .date-range {
            margin: 5px 0;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255,255,255,0.8);
        }

        .empty-state h2 {
            margin-bottom: 15px;
            font-size: 2rem;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .create-folder-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .create-folder-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .home-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.3);
            margin-right: 10px;
        }

        .home-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
            text-decoration: none;
            color: white;
        }

        @media (max-width: 768px) {
            .folders-grid {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 0 10px;
            }
            
            .folder-stats {
                flex-direction: column;
                gap: 10px;
            }
            
            .stat-item {
                display: flex;
                justify-content: space-between;
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <header class="header">
            <div class="header-content">
                <div class="header-title">
                    <h1>📁 Timeline Hub</h1>
                    <p>Manage and view timelines from multiple image folders</p>
                </div>
                <div class="user-info">
                    <span class="welcome-text">Welcome, <?php echo htmlspecialchars(Auth::getUsername()); ?>!</span>
                    <a href="test_api.php" class="home-btn">🧪 Test API</a>
                    <a href="logout.php" class="logout-btn">🚪 Logout</a>
                </div>
            </div>
        </header>
        
        <!-- Main Content -->
        <main>
            <?php if (empty($folders)): ?>
                <div class="empty-state">
                    <h2>📂 No Image Folders Found</h2>
                    <p>Create some image folders in the <code>images/</code> directory to get started.</p>
                    <p>For example: <code>images/driveway/</code>, <code>images/front/</code>, <code>images/garden/</code></p>
                    <button class="create-folder-btn" onclick="location.reload()">🔄 Refresh</button>
                </div>
            <?php else: ?>
                <div class="folders-grid">
                    <?php foreach ($folders as $folder): ?>
                        <a href="timeline.php?folder=<?php echo urlencode($folder['name']); ?>" class="folder-card">
                            <div class="folder-thumbnail">
                                <?php if ($folder['latest_image']): ?>
                                    <img src="<?php echo htmlspecialchars($folder['latest_image']['relative_path']); ?>" 
                                         alt="Latest from <?php echo htmlspecialchars($folder['display_name']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="no-image">📷</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="folder-info">
                                <h3><?php echo htmlspecialchars($folder['display_name']); ?></h3>
                                
                                <div class="folder-stats">
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo $folder['image_count']; ?></span>
                                        <span class="stat-label">Images</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo $folderManager->formatFileSize($folder['total_size']); ?></span>
                                        <span class="stat-label">Total Size</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number">
                                            <?php 
                                            if ($folder['latest_image'] && $folder['latest_image']['timestamp']) {
                                                $timestamp = $folder['latest_image']['timestamp'];
                                                $date = DateTime::createFromFormat('Ymd_His', $timestamp);
                                                echo $date ? $date->format('M j') : 'Recent';
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </span>
                                        <span class="stat-label">Latest</span>
                                    </div>
                                </div>
                                
                                <div class="folder-meta">
                                    <?php if ($folder['folder_stats'] && $folder['folder_stats']['date_range']): ?>
                                        <div class="date-range">
                                            📅 <?php echo htmlspecialchars($folder['folder_stats']['date_range']['first']); ?> 
                                            to <?php echo htmlspecialchars($folder['folder_stats']['date_range']['last']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($folder['folder_stats'] && $folder['folder_stats']['extensions']): ?>
                                        <div style="margin-top: 8px;">
                                            📁 Types: <?php echo implode(', ', array_keys($folder['folder_stats']['extensions'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
        
        <!-- Footer -->
        <footer style="text-align: center; margin-top: 40px; padding: 20px; color: rgba(255,255,255,0.7);">
            <p>Built with ❤️ for multi-folder monitoring</p>
            <p style="font-size: 0.9rem; margin-top: 10px;">
                Found <?php echo count($folders); ?> image folder<?php echo count($folders) !== 1 ? 's' : ''; ?>
            </p>
        </footer>
    </div>
    
    <!-- JavaScript for enhanced interactions -->
    <script>
        // Add loading states for folder cards
        document.querySelectorAll('.folder-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Add loading state
                const thumbnail = this.querySelector('.folder-thumbnail');
                if (thumbnail) {
                    thumbnail.style.opacity = '0.7';
                    thumbnail.innerHTML += '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 1.5rem;">Loading...</div>';
                }
            });
        });
        
        // Auto-refresh every 5 minutes to check for new folders
        setInterval(() => {
            // Only refresh if user is still on the page
            if (document.visibilityState === 'visible') {
                window.location.reload();
            }
        }, 300000); // 5 minutes
    </script>
</body>
</html>