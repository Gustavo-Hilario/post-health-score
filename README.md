## About This Project

This plugin is my application project for the **Fusion AI Coders Program**. The goal is to demonstrate problem-solving skills, effective use of AI tools, and ability to work with WordPress development.

## Initial Setup & Ideation

### AI Configuration

I use an ai-config-hub, a personal tool I built to centralize AI configurations across my projects. It manages Claude Code settings, MCP servers, and other AI tool configurations through a single command (`ai-init`), making it easy to maintain consistent AI workflows across different projects. This includes reusable slash commands, specialized agents, and a base `CLAUDE.md` configuration—essentially a Claude Code boilerplate for repeated tasks and coding patterns I use across all my projects.

### Research & Brainstorming

To explore the project options, I used the **context-a8c MCP plugin** to fetch and read the [AI Coders Program application post](https://fusionp2.wordpress.com/2025/12/17/ai-coders-program-application-project/) directly within Claude Code. This allowed me to:

1. **Review all five project options** without leaving my development environment
2. **Brainstorm ideas** with Claude Code for each option (Dashboard Widget, Settings Page, Admin Column, Content Add-On, Custom Block)
3. **Evaluate trade-offs** between complexity, visual impact, and practical utility

After discussing several ideas—including a "Content Freshness Dashboard" and "Post Publishing Checklist"—we landed on the **Post Health Score** concept for Option 3 (Admin Column). This choice balanced sophistication with simplicity, creating something genuinely useful for content managers while demonstrating solid WordPress development skills.

# Post Health Score

A WordPress plugin that adds a content quality "Health Score" column to the Posts admin list.

## Option Chosen: Admin Column

I chose **Option 3: Admin Column** from the available project options because:

1. **Practical Utility**: A content health score is genuinely useful for content managers and editors to quickly identify posts that need improvement.

2. **Technical Depth**: This option allows me to demonstrate understanding of WordPress hooks, post meta, taxonomies, and admin customization - all without being overly complex.

3. **Visual Impact**: The result is immediately visible and testable in the WordPress admin, making it easy to verify functionality.

4. **Creativity Opportunity**: The scoring system and visual presentation (grades, emojis, tooltips) allow for creative expression while solving a real problem.

## Plugin Goal

Add a "Health Score" column to the Posts list in WP Admin that grades each post based on content quality indicators:

| Criterion      | Requirement                        | Points |
| -------------- | ---------------------------------- | ------ |
| Word Count     | > 300 words                        | +1     |
| Featured Image | Has one set                        | +1     |
| Title Length   | 30-60 characters                   | +1     |
| Categories     | At least 1 (excl. "Uncategorized") | +1     |
| Tags           | At least 1                         | +1     |

**Grades:**

-   5 points = A+ (Excellent)
-   4 points = A (Great)
-   3 points = B (Good)
-   2 points = C (Needs Work)
-   1 point = D (Poor)
-   0 points = F (Failing)

## Planned Features

-   [x] Health Score column in Posts list
-   [x] Scoring based on 5 content quality criteria
-   [x] Letter grade with emoji indicator
-   [x] Hover tooltip showing detailed breakdown
-   [x] Color-coded badges for quick scanning
-   [x] Sortable column

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

-   **Claude Code** with **Claude Opus 4.5** - Primary development assistant for planning, code generation, and problem-solving
-   **Codex** - Code review and quality assurance (identified 5 issues that were subsequently fixed)

## Installation

1. Download or clone this repository
2. Upload to your WordPress site:
   - **Option A**: Unzip and upload the folder to `wp-content/plugins/`
   - **Option B**: Upload the .zip file directly via **Plugins > Add New > Upload Plugin** in wp-admin
3. Activate the plugin in WordPress admin

## Development Process

The plugin was developed incrementally, following a phased approach:

### What Worked Well

1. **Incremental Development**: Building the plugin step-by-step made it easier to test each feature in isolation and catch issues early.

2. **Claude Code Assistance**: The AI helped with:

    - Planning the implementation approach
    - Suggesting WordPress hooks and functions
    - Writing clean, documented PHP code
    - Generating CSS for tooltips and badges
    - Reviewing code structure

3. **Starting with README**: Writing the README first (before any code) helped clarify the project scope and requirements.

4. **MVP First**: Getting a basic working column before adding visual enhancements allowed for faster iteration.

### What I Learned

-   WordPress admin column hooks (`manage_posts_columns`, `manage_posts_custom_column`)
-   How to make columns sortable with `manage_edit-post_sortable_columns` and `pre_get_posts`
-   Using post meta to cache calculated values for efficient database queries
-   CSS-only tooltips as an alternative to JavaScript-based solutions

## Challenges & Solutions

### Challenge 1: Column Sorting

**Problem**: Health scores are calculated dynamically, but WordPress needs a database field to sort by.

**Solution**: Store the score as post meta (`_phs_health_score`) when the column is rendered and when posts are saved. This provides a cached value for efficient sorting.

### Challenge 2: "Uncategorized" Category

**Problem**: Every post has at least the "Uncategorized" category by default, which would make the category check always pass.

**Solution**: ~~Explicitly exclude category ID 1 (Uncategorized) from the count when checking for meaningful categories.~~ **Updated**: Use `get_option('default_category')` to dynamically get the actual default category ID, since it's configurable and may not always be ID 1.

### Challenge 3: Tooltip Positioning

**Problem**: Tooltips need to appear above the badge without being clipped by the table row.

**Solution**: Used CSS positioning with `position: absolute`, `bottom: 100%`, and a high `z-index` to ensure visibility.

## Code Review & Quality Assurance

Following the project tip to "ask another model to review your code", I used **Codex** to review the initial implementation. This cross-AI review identified 5 issues:

### Issues Found & Fixed

| Issue                    | Severity | Problem                                                   | Fix                                                  |
| ------------------------ | -------- | --------------------------------------------------------- | ---------------------------------------------------- |
| Hard-coded category ID   | Medium   | Assumed ID 1 is always "Uncategorized"                    | Use `get_option('default_category')`                 |
| Unnecessary DB writes    | Medium   | `update_post_meta` ran on every page view                 | Removed from render, kept only on `save_post`        |
| ASCII-only functions     | Medium   | `strlen()` and `str_word_count()` fail for non-Latin text | Use `mb_strlen()` and `preg_split()`                 |
| Non-translatable strings | Low      | Labels weren't wrapped in `__()`                          | Added `load_plugin_textdomain()` and wrapped strings |
| CSS scope too broad      | Low      | Styles loaded on all post types                           | Added `$screen->post_type` check                     |

### Lessons Learned

1. **Cross-AI review is valuable**: Having a different AI model review the code caught issues that weren't obvious during initial development.

2. **Internationalization matters**: Even for a demo project, proper i18n shows attention to WordPress standards.

3. **Performance considerations**: Avoiding unnecessary database writes on read operations is important for scalability.

## Screenshots

After activating the plugin, you'll see a "Health Score" column in the Posts list:

-   Each post shows a grade (A+ through F) with a corresponding emoji
-   Hover over any grade to see the detailed breakdown
-   Click the column header to sort posts by score

## File Structure

```
post-health-score/
├── post-health-score.php    # Main plugin file (~310 lines)
├── assets/
│   └── css/
│       └── admin.css        # Admin styling (~107 lines)
└── README.md                # This file
```

## License

GPL v2 or later

---

Built for the [Fusion AI Coders Program](https://fusionp2.wordpress.com/2025/12/17/ai-coders-program-application-project/) application.
