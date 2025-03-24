# Vibe Jam 2025 Website TODO

## Database Setup

- [ ] Set up SQLite database
  - [ ] Create submissions table with fields:
    - id (PRIMARY KEY)
    - title
    - creator
    - description
    - category
    - screenshot_url
    - submission_date
    - game_url
  - [ ] Create indexes for common queries (title, category)

## Google Form Integration

- [ ] Research Google Forms API integration
  - [ ] Determine how to access submission data
  - [ ] Plan data synchronization strategy
  - [ ] Create script to import submissions from Google Forms to SQLite

## Screenshot Handling

- [ ] Research and decide on screenshot storage solution
  - [ ] Options to consider:
    - Local storage with proper optimization
    - CDN integration
    - Cloud storage (e.g., S3, Google Cloud Storage)
  - [ ] Implement screenshot optimization pipeline

## Frontend Development

- [ ] Implement responsive CSS

  - [ ] Create mobile-first CSS structure
  - [ ] Set up CSS variables for consistent theming
  - [ ] Implement media queries (mobile breakpoint: 640px)
  - [ ] Create responsive grid/flexbox layout system

- [ ] Create responsive card layout
  - [ ] Desktop layout (horizontal cards)
    - [ ] Screenshot on left
    - [ ] Content on right
  - [ ] Mobile layout (vertical cards)
    - [ ] Screenshot on top
    - [ ] Content below
  - [ ] Implement proper image sizing and optimization

## Pagination System

- [ ] Implement PHP pagination
  - [ ] Create paginated query structure
  - [ ] Define items per page
  - [ ] Add page navigation
  - [ ] Implement lazy loading for smooth scrolling

## Filtering System

- [ ] Create filtering functionality
  - [ ] Filter by:
    - [ ] Category
    - [ ] Title (search)
    - [ ] Creator
  - [ ] Implement filter UI
  - [ ] Create efficient SQL queries

## Performance Optimization

- [ ] Implement performance measures
  - [ ] Image optimization and lazy loading
  - [ ] SQLite query optimization
  - [ ] CSS minification
  - [ ] Browser caching
  - [ ] Implement infinite scroll or efficient pagination

## Testing

- [ ] Create test plan
  - [ ] Mobile responsiveness testing
  - [ ] Performance testing
  - [ ] Database query optimization testing
  - [ ] Cross-browser compatibility

## Documentation

- [ ] Create documentation for:
  - [ ] Database schema
  - [ ] Google Form integration
  - [ ] Deployment process
  - [ ] Maintenance procedures
