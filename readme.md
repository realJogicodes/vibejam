# Vibe Jam 2025 Website - README

Hey Pieter,

This is the custom-built vanilla single file index.php website for your **2025 Vibe Coding Game Jam** – a platform to showcase AI-vibecoded browser games. I’ve kept it lean, fast, and indie-style, just how you like it. Below’s everything you need to know about this project.

## What This Is

This site is the hub for Vibe Jam 2025 – a competition where devs use AI to code browser-based video games (at least 80% AI-written). It’s live at [jam.pieter.com](http://jam.pieter.com) for submissions, and this codebase displays all the entries with screenshots, playable links, and filters. Right now, it’s handling ~500 submissions, with room to scale to 1000+ by the April 1st, 2025 deadline.

The jury (you, @karpathy, @timsoret, @mrdoob, @s13k\_) and sponsors (@boltdotnew, @coderabbitai) are baked in, with a clean UI to hype the event and showcase the games.

## Tech Stack

No frameworks, no bloat – pure indie vibes:

- **PHP**: Vanilla PHP powers the backend. One `index.php` file does all the heavy lifting.
- **SQLite3**: A lightweight database (`vibejam.db`) stores submissions, jury, and sponsors. No MySQL overhead, lean, fast, extenendable
- **HTML/CSS**: Handwritten, mobile-responsive CSS with a Recursive font from Google Fonts for that clean look. Easy to swap if you don't like it (cmd f for recursive)
- **No JavaScript**: Yep, fully static where possible – fast and simple.
- **X Integration**: Jury and sponsor avatars pulled in using their Twitter handles.

## How It Works

### File Structure

/vibejam
├── database/
│ ├── schema.sql # Database structure and initial data (jury, sponsors)
│ ├── sample_data.sql # Sample game submissions for testing
│ └── vibejam.db # A sample SQLite3 database file with mock data
├── index.php # The single PHP file serving the site
├── project_context.md # Context for the LLMs during development
├── twitter-bios-sponsors.md # Sponsor Twitter bio data, context for the LLMs during development
├── twitter-bios-jury.md # Jury Twitter bio data, context for the LLMs during development
└── README.md # This file!

### The Core: `index.php`

- **Database Connection**: Connects to `vibejam.db` using SQLite3.
- **Queries**: Pulls submissions, jury, and sponsors from the DB.
- **Rendering**: Dynamically generates HTML for the homepage, submission grid, jury, and sponsor sections.
- **Features**:
  - **Pagination**: 12 games per page, can be tweaked to be more or less. Handles 500+ entries smoothly.
  - **Filters**: Search by title/creator, filter by category (FPS, Platformer, etc.).
  - **Responsive Design**: Looks dope on mobile and desktop.

### Database: `vibejam.db`

- **Submissions Table**: Stores game details (title, creator, URL, screenshot, AI %, etc.).
- **Jury/Sponsors Tables**: Simple username storage for display.
- **Schema**: Defined in `schema.sql` with validation (e.g., AI code ≥ 80%, load time < 5s).
- **Sample Data**: `sample_data.sql` seeds it with 12 test games.

## Key Features

- **Mobile-Friendly**: Fully responsive – submission cards stack on small screens.
- **Fast**: No loading screens, no heavy downloads (enforces your rules!).
- **Scalable**: SQLite3 handles 1000+ entries fine for now; pagination keeps it snappy.
- **Social Proof**: Jury and sponsor sections link to X profiles with avatars. Extendable.
- **Submit CTA**: Big button links to [jam.pieter.com](http://jam.pieter.com).

## Setup Instructions

1. **Clone/Download**: Grab this repo.
2. **PHP Server**: Run `php -S localhost:8000` in the root folder.
3. **Database Setup**:
   - Run `sqlite3 database/vibejam.db < database/schema.sql` to create the DB and load jury/sponsors.
   - Run `sqlite3 database/vibejam.db < database/sample_data.sql` to add sample games.
     Sample database is included with mock data. You want to make a script to fill it with the google form entries.
4. **Visit**: Open `http://localhost:8000` in your browser.
5. **Deploy**: Drop it on any PHP-enabled server (e.g., your hosting setup).

## For You to Know

- **Deadline**: Extended to April 1st, 2025 – reflected in the UI.
- **Customizable**: Add more jury/sponsors in `schema.sql` or tweak `index.php` styling.

## Next Steps

- **Live Submissions**: Hook up a form to insert into `submissions` table (right now, it’s manual DB edits).
  -> People can submit their entry and supply screenshots themselves.
- **Analytics**: Could add basic tracking if you want to see which games get clicks.
- **Polish**: Any vibe tweaks you want? Colors, fonts, emojis? Happy to keep tweaking.
