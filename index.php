<?php
// Database connection
try {
    $db = new SQLite3('database/vibejam.db');
} catch (Exception $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Get competition details
$jury_members = $db->query("SELECT username FROM jury_members ORDER BY username");
$sponsors = $db->query("SELECT username FROM sponsors ORDER BY username");

// Pagination settings
$items_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Build the query based on filters
$where_clauses = [];
$params = [];
$param_types = [];

if (!empty($_GET['search'])) {
    $where_clauses[] = "(title LIKE ? OR creator LIKE ?)";
    $search_term = '%' . $_GET['search'] . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types[] = SQLITE3_TEXT;
    $param_types[] = SQLITE3_TEXT;
}

if (!empty($_GET['category'])) {
    $where_clauses[] = "category = ?";
    $params[] = $_GET['category'];
    $param_types[] = SQLITE3_TEXT;
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM submissions $where_sql";
$stmt = $db->prepare($count_query);
for ($i = 0; $i < count($params); $i++) {
    $stmt->bindValue($i + 1, $params[$i], $param_types[$i]);
}
$total_count = $stmt->execute()->fetchArray()[0];
$total_pages = ceil($total_count / $items_per_page);

// Get submissions with pagination
$query = "SELECT * FROM submissions $where_sql ORDER BY submission_date DESC LIMIT ? OFFSET ?";
$stmt = $db->prepare($query);
for ($i = 0; $i < count($params); $i++) {
    $stmt->bindValue($i + 1, $params[$i], $param_types[$i]);
}
$stmt->bindValue(count($params) + 1, $items_per_page, SQLITE3_INTEGER);
$stmt->bindValue(count($params) + 2, $offset, SQLITE3_INTEGER);
$result = $stmt->execute();

// Get unique categories for filter buttons
$categories = $db->query("SELECT DISTINCT category FROM submissions ORDER BY category");
$category_list = [];
while ($cat = $categories->fetchArray()) {
    if (!empty($cat['category'])) {
        $category_list[] = $cat['category'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Recursive:wght@300..1000&display=swap" rel="stylesheet">
    <title>2025 Vibe Coding Game Jam - Submissions</title>
    <style>

        .recursive-500 {
          font-family: "Recursive", sans-serif;
          font-optical-sizing: auto;
          font-weight: 500;
          font-style: normal;
          font-variation-settings:
            "slnt" 0,
            "CASL" 0,
            "CRSV" 0.5,
            "MONO" 0;
        }

        :root {
            --primary-color: #2d3748;
            --secondary-color: #4a5568;
            --background-color: #f7fafc;
            --text-color: #1a202c;
            --border-radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .competition-info {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .competition-info h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .competition-info p {
            font-size: 1.2rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
        }

        .jury-sponsors {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .jury-sponsors > div {
            flex: 1;
            min-width: 250px;
        }

        .tag {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            margin: 0.25rem;
        }

        .deadline {
            font-weight: bold;
            color: #e53e3e;
        }

        .submission-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }

        .submission-meta span {
            color: var(--secondary-color);
        }

        .filters {
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: white;
            border-radius: var(--border-radius);
        }

        .submissions-grid {
            display: grid;
            gap: 2rem;
            grid-template-columns: 1fr;
            max-width: 1400px;
            margin: 0 auto;
        }

        @media (max-width: 640px) {
            .submissions-grid {
               padding: 2rem;
            }
        }

        .submission-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .card-content {
            padding: 1.5rem;
            flex: 1;
        }

        .submission-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .submission-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .submission-creator {
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .submission-description {
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .pagination {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            background: white;
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--primary-color);
        }

        .pagination a.active {
            background: var(--primary-color);
            color: white;
        }

        @media (min-width: 640px) {
            .submissions-grid {
                grid-template-columns: repeat(1, 1fr);
            }

            .submission-card {
                flex-direction: row;
            }

            .submission-image {
                width: 400px;
                height: 250px;
                object-fit: cover;
            }
        }

        @media (min-width: 1024px) {
            .submissions-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .submission-card {
                flex-direction: column;
            }

            .submission-image {
                width: 100%;
                height: 300px;
            }
        }

        .submit-button {
            display: inline-block;
            background: #10B981;
            color: white;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.2s;
            margin: 1rem 0;
        }

        .submit-button:hover {
            background: #059669;
        }

        .filter-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .search-box {
            display: flex;
            gap: 0.5rem;
        }

        .search-box input {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: var(--border-radius);
        }

        .search-box button {
            padding: 0.5rem 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
        }

        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .category-button {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid var(--primary-color);
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--primary-color);
            transition: all 0.2s;
        }

        .category-button:hover {
            background: var(--primary-color);
            color: white;
        }

        .category-button.active {
            background: var(--primary-color);
            color: white;
        }

        .jury-members {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            margin: 1rem 0;
        }

        .jury-member {
            display: flex;
            align-items: center;
            background: rgb(237, 240, 243);
            border-radius: 50px;
            padding: 0.5rem;
            transition: transform 0.2s ease;
            text-decoration: none;
            color: var(--text-color);
        }

        .jury-member:hover {
            transform: scale(1.05);
        }

        .jury-member img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.75rem;
        }

        .jury-member span {
            padding-right: 1rem;
            font-weight: 500;
        }

        .play-button {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: bold;
            transition: all 0.2s;
            margin-top: 1rem;
        }

        .play-button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        @media (max-width: 640px) {
            .filter-form {
                gap: 1rem;
            }

            .search-box {
                flex-direction: column;
            }

            .search-box button {
                width: 100%;
            }

            .category-filters {
                justify-content: center;
            }
        }

        .emoji-space {
            margin-right: 0.5rem;
            display: inline-block;
        }

        .jury-section {
            background: white;
            padding: 2rem;
            margin-top: 4rem;
            border-top: 1px solid #e2e8f0;
        }

        .jury-grid {
            display: grid;
            gap: 2rem;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            max-width: 1400px;
            margin: 0 auto;
        }

        .jury-card {
            background: rgb(237, 240, 243);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: transform 0.2s ease;
        }

        .jury-card:hover {
            transform: translateY(-5px);
        }

        .jury-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-color);
            padding: 1rem;
        }

        .jury-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-right: 1rem;
            object-fit: cover;
        }

        .jury-content {
            flex: 1;
        }

        .jury-name {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .jury-bio {
            font-size: 0.9rem;
            color: var(--secondary-color);
        }

        @media (max-width: 640px) {
            .jury-link {
                flex-direction: column;
                text-align: center;
            }

            .jury-image {
                margin-right: 0;
                margin-bottom: 1rem;
            }
        }

        .sponsors-section {
            background: white;
            padding: 3rem 2rem;
            margin-top: 4rem;
            border-top: 1px solid #e2e8f0;
        }

        .sponsors-container {
            display: grid;
            gap: 3rem;
            grid-template-columns: 1fr; /* Single column for mobile */
            max-width: 1400px;
            margin: 0 auto;
        }

        @media (min-width: 768px) {
            .sponsors-container {
                grid-template-columns: repeat(2, 1fr); /* Two columns on larger screens */
            }
        }

        .sponsor-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .sponsor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .sponsor-banner {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .sponsor-info {
            padding: 1.5rem;
            background: rgb(237, 240, 243);
        }

        .sponsor-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-color);
        }

        .sponsor-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-right: 1.5rem;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sponsor-content {
            flex: 1;
        }

        .sponsor-name {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .sponsor-bio {
            font-size: 1rem;
            color: var(--secondary-color);
            line-height: 1.5;
        }

        .footer-section {
            background: white;
            padding: 2rem 0;
            margin-top: 4rem;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }

        .footer-text {
            font-size: 0.9rem;
            color: var(--secondary-color);
        }

        .footer-text a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .sponsor-link {
                flex-direction: column;
                text-align: center;
            }

            .sponsor-image {
                margin-right: 0;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <section class="competition-info">
            <p style="font-size: 1.2rem; color: #e53e3e;" class="recursive-500"><span class="emoji-space">🤖</span>AI-Powered Game Dev Jam</p>
            <h1 class="recursive-500">2025 Vibe Coding Game Jam</h1>
            <p class="recursive-500">Push AI to its limits and build something wild</p>
                        <p style="font-size: 1.2rem; margin-bottom: 1.5rem;" class="recursive-500"><span class="emoji-space">👾</span>Can you make the sickest AI-powered online game?</p>
            <ul style="list-style: none; margin: 1.5rem 0; text-align: left; max-width: 600px; margin-left: auto; margin-right: auto;">
              <li><span class="emoji-space">🏆</span>Show off your skills – Get judged by game devs</li>
              <li><span class="emoji-space">📣</span>Go viral – Winners get a big Twitter shoutout</li>
              <li><span class="emoji-space">⏰</span>Make a game by April 1st, 2025</li>
                <li><span class="emoji-space">🧠</span>Write it with AI-generated code </li>
                <li><span class="emoji-space">🌐</span>No logins, no downloads</li>
            </ul>

            <p><a href="http://jam.pieter.com" class="submit-button" style="background: #e53e3e;"><span class="emoji-space">🔥</span>Submit Your Game Now!</a></p>
            
            <div class="jury-sponsors">
                <div>
                    <h2>Jury Members</h2>
                    <div class="jury-members">
                    <?php 
                    $ordered_jury = array();
                    while ($jury = $jury_members->fetchArray()) {
                        if ($jury['username'] === '@levelsio') {
                            $levelsio = $jury;
                        } else {
                            $ordered_jury[] = $jury;
                        }
                    }
                    // Sort other jury members alphabetically
                    usort($ordered_jury, function($a, $b) {
                        return strcmp($a['username'], $b['username']);
                    });
                    // Add levelsio at the end
                    if (isset($levelsio)) {
                        $ordered_jury[] = $levelsio;
                    }
                    
                    foreach ($ordered_jury as $jury): ?>
                        <a href="https://x.com/<?php echo htmlspecialchars(ltrim($jury['username'], '@')); ?>" target="_blank" class="jury-member">
                            <img src="https://unavatar.io/twitter/<?php echo htmlspecialchars(ltrim($jury['username'], '@')); ?>" alt="Profile picture of <?php echo htmlspecialchars($jury['username']); ?>">
                            <span><?php echo htmlspecialchars($jury['username']); ?></span>
                        </a>
                    <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <h2>Sponsors</h2>
                    <div class="jury-members">
                    <?php while ($sponsor = $sponsors->fetchArray()): ?>
                        <a href="https://x.com/<?php echo htmlspecialchars(ltrim($sponsor['username'], '@')); ?>" target="_blank" class="jury-member">
                            <img src="https://unavatar.io/twitter/<?php echo htmlspecialchars(ltrim($sponsor['username'], '@')); ?>" alt="Profile picture of <?php echo htmlspecialchars($sponsor['username']); ?>">
                            <span><?php echo htmlspecialchars($sponsor['username']); ?></span>
                        </a>
                    <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <p><strong>Rules:</strong> Games must be 80% AI-coded, web-accessible, and load instantly!</p>
        </section>

        <section class="filters">
            <form action="" method="GET" class="filter-form">
                <div class="category-filters">
                    <a href="?" class="category-button <?php echo empty($_GET['category']) ? 'active' : ''; ?>">
                        <span class="emoji-space">🎮</span>All Games
                    </a>
                    <?php foreach ($category_list as $category): ?>
                        <a href="?category=<?php echo urlencode($category); ?>" 
                           class="category-button <?php echo ($_GET['category'] ?? '') === $category ? 'active' : ''; ?>">
                            <?php 
                            $emoji = match($category) {
                                'Death match' => '⚔️',
                                'FPS' => '🎯',
                                'Platformer' => '🏃',
                                'Real Time Strategy' => '🏰',
                                'Simulator' => '🎛️',
                                default => '🎲'
                            };
                            echo '<span class="emoji-space">' . $emoji . '</span>' . htmlspecialchars($category);
                            ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search by title or creator"
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <button type="submit"><span class="emoji-space">🔍</span>Search</button>
                </div>
            </form>
        </section>

        <main class="submissions-grid">
            <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
                <article class="submission-card">
                    <img class="submission-image" 
                         src="<?php echo htmlspecialchars($row['screenshot_url']); ?>" 
                         alt="Screenshot of <?php echo htmlspecialchars($row['title']); ?>">
                    <div class="card-content">
                        <h2 class="submission-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                        <p class="submission-creator">by <?php echo htmlspecialchars($row['creator']); ?></p>
                        <p class="submission-description"><?php echo htmlspecialchars($row['description']); ?></p>
                        <div class="submission-meta">
                            <span><span class="emoji-space">🎮</span><?php echo $row['engine_used'] ? htmlspecialchars($row['engine_used']) : 'Custom Engine'; ?></span>
                            <span><?php echo $row['is_multiplayer'] ? '<span class="emoji-space">👥</span>Multiplayer' : '<span class="emoji-space">👤</span>Single Player'; ?></span>
                            <?php if ($row['username_required']): ?>
                                <span><span class="emoji-space">👤</span>Username Required</span>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo htmlspecialchars($row['game_url']); ?>" target="_blank" 
                           class="play-button"><span class="emoji-space">▶️</span>Play Game</a>
                    </div>
                </article>
            <?php endwhile; ?>
        </main>

        <nav class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>"><span class="emoji-space">⬅️</span>Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?>" 
                   class="<?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Next<span class="emoji-space" style="margin: 0 0 0 0.5rem">➡️</span></a>
            <?php endif; ?>
        </nav>

        <section class="sponsors-section">
            <div class="container">
                <h2 style="text-align: center; margin-bottom: 3rem;" class="recursive-500">Vibe Jam Sponsors</h2>
                <div class="sponsors-container">
                    <?php
                    // Reset sponsors result pointer
                    $sponsors->reset();
                    while ($sponsor = $sponsors->fetchArray()):
                        $username = htmlspecialchars(ltrim($sponsor['username'], '@'));
                        $banner_url = $sponsor['username'] === '@boltdotnew'
                            ? 'https://pbs.twimg.com/profile_banners/2279695508/1727979389/1500x500'
                            : 'https://pbs.twimg.com/profile_banners/1651729524579766272/1729919845/1500x500';
                    ?>
                        <div class="sponsor-card">
                            <img class="sponsor-banner"
                                 src="<?php echo $banner_url; ?>"
                                 alt="Banner for <?php echo htmlspecialchars($sponsor['username']); ?>">
                            <div class="sponsor-info">
                                <a href="https://x.com/<?php echo $username; ?>" target="_blank" class="sponsor-link">
                                    <img class="sponsor-image"
                                         src="https://unavatar.io/twitter/<?php echo $username; ?>"
                                         alt="Profile picture of <?php echo htmlspecialchars($sponsor['username']); ?>">
                                    <div class="sponsor-content">
                                        <h3 class="sponsor-name"><?php echo htmlspecialchars($sponsor['username']); ?></h3>
                                        <p class="sponsor-bio">
                                            <?php
                                            switch($sponsor['username']) {
                                                case '@boltdotnew':
                                                    echo 'Platform for prompting, running, editing, and deploying full-stack web and mobile apps @boltnew since 2014.';
                                                    break;
                                                case '@coderabbitai':
                                                    echo 'San Francisco-based AI code review platform @coderabbitai, supercharging dev teams since 2023.';
                                                    break;
                                                default:
                                                    echo 'Vibe Jam sponsor';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
    </div>

    <section class="jury-section">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 2rem;">The Jury</h2>
            <div class="jury-grid">
                <?php
                // Reset jury_members result pointer
                $jury_members->reset();
                while ($jury = $jury_members->fetchArray()):
                    $username = htmlspecialchars(ltrim($jury['username'], '@'));
                ?>
                    <div class="jury-card">
                        <a href="https://x.com/<?php echo $username; ?>" target="_blank" class="jury-link">
                            <img class="jury-image"
                                 src="https://unavatar.io/twitter/<?php echo $username; ?>"
                                 alt="Profile picture of <?php echo htmlspecialchars($jury['username']); ?>">
                            <div class="jury-content">
                                <h3 class="jury-name"><?php echo htmlspecialchars($jury['username']); ?></h3>
                                <p class="jury-bio">
                                    <?php
                                    switch($jury['username']) {
                                        case '@karpathy':
                                            echo 'AI research pioneer building @EurekaLabsAI. Ex-Director of AI @Tesla, founding member @OpenAI, and Stanford PhD.';
                                            break;
                                        case '@timsoret':
                                            echo 'Founder of @OddTalesGames, directing @TLN_Game. Expert in art direction, cinematography, and tech art.';
                                            break;
                                        case '@mrdoob':
                                            echo 'Creator of @ThreeJS, web graphics innovator, and self-proclaimed "award-losing non-creative junior developer."';
                                            break;
                                        case '@s13k_':
                                            echo 'Vibe code consultant and web development enthusiast at s13k.dev.';
                                            break;
                                        case '@levelsio':
                                            echo 'Indie maker and serial entrepreneur behind @PhotoAI, @InteriorAI, @Nomads, @RemoteOK, and more.';
                                            break;
                                        default:
                                            echo 'Vibe Jam jury member';
                                    }
                                    ?>
                                </p>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <footer class="footer-section">
        <div class="container">
            <p class="footer-text">Website vibe coded by <a href="https://x.com/jogicodes" target="_blank">@jogicodes</a> using vanilla PHP, vanilla CSS, SQLite3 with help from Claude 3.7 and Grok 3 with some sprinkles of GPT</p>
        </div>
    </footer>

</body>
</html>