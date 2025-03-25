-- Delete existing data
DELETE FROM submissions;

-- Insert sample submissions
INSERT INTO submissions (
    title, 
    creator, 
    description, 
    category, 
    screenshot_url, 
    game_url, 
    ai_code_percentage, 
    engine_used, 
    is_multiplayer, 
    domain_url,
    loading_time_ms,
    username_required
) VALUES 
    (
        'Arena Mayhem',
        '@arenachamp',
        'Fast-paced arena combat with AI-generated weapons and maps',
        'Death match',
        'https://picsum.photos/800/600?random=1',
        'https://arena-mayhem.example.com',
        95,
        'ThreeJS',
        1,
        'arena-mayhem.example.com',
        1200,
        1
    ),
    (
        'Cyber Strike',
        '@cyberhunter',
        'Futuristic FPS with procedurally generated levels',
        'FPS',
        'https://picsum.photos/800/600?random=2',
        'https://cyberstrike.example.com',
        85,
        'ThreeJS',
        1,
        'cyberstrike.example.com',
        2000,
        1
    ),
    (
        'Pixel Runner',
        '@pixelartist',
        'AI-generated endless runner with dynamic obstacles',
        'Platformer',
        'https://picsum.photos/800/600?random=3',
        'https://pixelrunner.example.com',
        90,
        'ThreeJS',
        0,
        'pixelrunner.example.com',
        500,
        0
    ),
    (
        'Empire Builder',
        '@strategist',
        'Build and manage your empire with AI-powered resource management',
        'Real Time Strategy',
        'https://picsum.photos/800/600?random=4',
        'https://empirebuilder.example.com',
        88,
        'ThreeJS',
        1,
        'empirebuilder.example.com',
        3000,
        1
    ),
    (
        'Space Station Manager',
        '@spacesim',
        'Manage a space station with realistic AI-driven physics',
        'Simulator',
        'https://picsum.photos/800/600?random=5',
        'https://spacestation.example.com',
        92,
        'ThreeJS',
        0,
        'spacestation.example.com',
        800,
        0
    ),
    (
        'Neon Brawl',
        '@neondev',
        'Cyberpunk themed battle arena with unique AI fighters',
        'Death match',
        'https://picsum.photos/800/600?random=6',
        'https://neonbrawl.example.com',
        87,
        'ThreeJS',
        1,
        'neonbrawl.example.com',
        1500,
        1
    ),
    (
        'Tactical Ops',
        '@tactician',
        'Squad-based tactical FPS with AI-driven team mechanics',
        'FPS',
        'https://picsum.photos/800/600?random=7',
        'https://tacticalops.example.com',
        83,
        'ThreeJS',
        1,
        'tacticalops.example.com',
        2500,
        1
    ),
    (
        'Cloud Jumper',
        '@clouddev',
        'Sky-high platforming adventure with dynamic weather effects',
        'Platformer',
        'https://picsum.photos/800/600?random=8',
        'https://cloudjumper.example.com',
        89,
        'ThreeJS',
        0,
        'cloudjumper.example.com',
        1000,
        0
    ),
    (
        'Star Commander',
        '@starcommand',
        'Interstellar real-time strategy with AI fleet management',
        'Real Time Strategy',
        'https://picsum.photos/800/600?random=9',
        'https://starcommander.example.com',
        86,
        'ThreeJS',
        1,
        'starcommander.example.com',
        2800,
        1
    ),
    (
        'Flight Master',
        '@flightsim',
        'Advanced flight simulator with realistic physics',
        'Simulator',
        'https://picsum.photos/800/600?random=10',
        'https://flightmaster.example.com',
        91,
        'ThreeJS',
        0,
        'flightmaster.example.com',
        1800,
        0
    ),
    (
        'Puzzle Quest',
        '@puzzlemaster',
        'Mind-bending puzzle game with AI-generated challenges',
        'Other',
        'https://picsum.photos/800/600?random=11',
        'https://puzzlequest.example.com',
        84,
        'ThreeJS',
        0,
        'puzzlequest.example.com',
        1600,
        0
    ),
    (
        'Rhythm Beats',
        '@beatmaker',
        'AI-generated music rhythm game',
        'Other',
        'https://picsum.photos/800/600?random=12',
        'https://rhythmbeats.example.com',
        93,
        'ThreeJS',
        0,
        'rhythmbeats.example.com',
        400,
        0
    );