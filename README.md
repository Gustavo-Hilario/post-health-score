# Post Health Score

A WordPress plugin that adds a content quality "Health Score" column to the Posts admin list.

## About This Project

This plugin is my application project for the **Fusion AI Coders Program**. The goal is to demonstrate problem-solving skills, effective use of AI tools, and ability to work with WordPress development.

## Option Chosen: Admin Column

I chose **Option 3: Admin Column** from the available project options because:

1. **Practical Utility**: A content health score is genuinely useful for content managers and editors to quickly identify posts that need improvement.

2. **Technical Depth**: This option allows me to demonstrate understanding of WordPress hooks, post meta, taxonomies, and admin customization - all without being overly complex.

3. **Visual Impact**: The result is immediately visible and testable in the WordPress admin, making it easy to verify functionality.

4. **Creativity Opportunity**: The scoring system and visual presentation (grades, emojis, tooltips) allow for creative expression while solving a real problem.

## Plugin Goal

Add a "Health Score" column to the Posts list in WP Admin that grades each post based on content quality indicators:

| Criterion | Requirement | Points |
|-----------|-------------|--------|
| Word Count | > 300 words | +1 |
| Featured Image | Has one set | +1 |
| Title Length | 30-60 characters | +1 |
| Categories | At least 1 (excl. "Uncategorized") | +1 |
| Tags | At least 1 | +1 |

**Grades:**
- 5 points = A+ (Excellent)
- 4 points = A (Great)
- 3 points = B (Good)
- 2 points = C (Needs Work)
- 1 point = D (Poor)
- 0 points = F (Failing)

## Planned Features

- [x] Health Score column in Posts list
- [x] Scoring based on 5 content quality criteria
- [x] Letter grade with emoji indicator
- [x] Hover tooltip showing detailed breakdown
- [x] Color-coded badges for quick scanning
- [x] Sortable column

## Implementation Plan

### Phase 1: MVP
1. Create minimal plugin structure
2. Add column to Posts list
3. Implement basic scoring logic

### Phase 2: Enhancement
4. Add grade letters and emoji display
5. Add CSS styling for badges
6. Add tooltip with detailed breakdown

### Phase 3: Polish
7. Make column sortable
8. Final documentation and cleanup

## AI Tools Used

- **Claude Code** with **Claude Opus 4.5** - Primary development assistant for planning, code generation, and problem-solving

## Installation

1. Download or clone this repository
2. Upload to `wp-content/plugins/post-health-score/`
3. Activate the plugin in WordPress admin

## Development Process

*This section will be updated as development progresses.*

## Challenges & Solutions

*This section will be updated as challenges are encountered and resolved.*

## Screenshots

*Screenshots will be added once the plugin is functional.*

---

Built for the [Fusion AI Coders Program](https://fusionp2.wordpress.com/2025/12/17/ai-coders-program-application-project/) application.
