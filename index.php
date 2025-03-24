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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2025 Vibe Coding Game Jam - Submissions</title>
    <style>
        :root {
            --primary-color: #2d3748;
            --secondary-color: #4a5568;
            --background-color: #f7fafc;
            --text-color: #1a202c;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            padding: 20px;
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
            border-radius: 8px;
            box-shadow: var(--card-shadow);
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
            border-radius: 1rem;
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
            border-radius: 8px;
            box-shadow: var(--card-shadow);
        }

        .submissions-grid {
            display: grid;
            gap: 2rem;
            grid-template-columns: 1fr;
        }

        .submission-card {
            background: white;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .card-content {
            padding: 1rem;
        }

        .submission-image {
            width: 100%;
            height: 200px;
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
            border-radius: 4px;
            text-decoration: none;
            color: var(--primary-color);
            box-shadow: var(--card-shadow);
        }

        .pagination a.active {
            background: var(--primary-color);
            color: white;
        }

        @media (min-width: 640px) {
            .submissions-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .submission-card {
                flex-direction: row;
            }

            .submission-image {
                width: 200px;
                height: 100%;
            }
        }

        @media (min-width: 1024px) {
            .submissions-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <section class="competition-info">
            <h1>2025 Vibe Coding Game Jam</h1>
            <p>The first game jam for AI vibecoded games</p>
            <p class="deadline">Submission Deadline: April 1, 2025</p>
            
            <div class="jury-sponsors">
                <div>
                    <h2>Jury Members</h2>
                    <?php while ($jury = $jury_members->fetchArray()): ?>
                        <span class="tag"><?php echo htmlspecialchars($jury['username']); ?></span>
                    <?php endwhile; ?>
                </div>
                <div>
                    <h2>Sponsors</h2>
                    <?php while ($sponsor = $sponsors->fetchArray()): ?>
                        <span class="tag"><?php echo htmlspecialchars($sponsor['username']); ?></span>
                    <?php endwhile; ?>
                </div>
            </div>

            <p><strong>Rules:</strong> Games must be 80% AI-coded, web-accessible, and load instantly!</p>
        </section>

        <section class="filters">
            <form action="" method="GET">
                <input type="text" name="search" placeholder="Search by title or creator" 
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php
                    $categories = $db->query("SELECT DISTINCT category FROM submissions ORDER BY category");
                    while ($category = $categories->fetchArray()) {
                        $selected = ($_GET['category'] ?? '') === $category['category'] ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($category['category']) . "' $selected>" . 
                             htmlspecialchars($category['category']) . "</option>";
                    }
                    ?>
                </select>
                <button type="submit">Filter</button>
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
                            <span><?php echo $row['ai_code_percentage']; ?>% AI-coded</span>
                            <span><?php echo $row['engine_used'] ? htmlspecialchars($row['engine_used']) : 'Custom Engine'; ?></span>
                            <span><?php echo $row['is_multiplayer'] ? 'Multiplayer' : 'Single Player'; ?></span>
                            <?php if ($row['username_required']): ?>
                                <span>Username Required</span>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo htmlspecialchars($row['game_url']); ?>" target="_blank" 
                           class="play-button">Play Game</a>
                    </div>
                </article>
            <?php endwhile; ?>
        </main>

        <nav class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?>" 
                   class="<?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
            <?php endif; ?>
        </nav>
    </div>
</body>
</html>