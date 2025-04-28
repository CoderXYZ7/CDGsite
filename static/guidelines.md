# Style Guidelines

Based on the provided CSS, here's a comprehensive style guideline to ensure consistent design across your website:

## Color Palette

### Primary Colors

- **Primary Blue**: `#1a365d` (used for navigation, headers, accents)
- **Accent Red**: `#e53e3e` (used for buttons, highlights, interactive elements)
- **Attention Gradient**: `#e53e3e` to `#ff6a3d` (used for important alerts)

### Neutral Colors

- **White**: `#ffffff` (background, text on dark surfaces)
- **Light Gray**: `#f8f9fa` (backgrounds)
- **Medium Gray**: `#666` (secondary text)
- **Dark Gray**: `#333` (body text)

## Typography

### Font Family

- **Primary**: System UI stack (`system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif`)
- **Fallback**: Sans-serif

### Font Sizes

- **Base**: `1rem` (16px)
- **Small**: `0.875rem` (14px)
- **Medium**: `1.2rem` (19px)
- **Large**: `1.5rem` (24px)
- **Extra Large**: `2.5rem` (40px) - for main headings

### Line Height

- **Base**: `1.6` for optimal readability

## Layout

### Grid System

- **Cards Grid**: Responsive grid with `minmax(300px, 1fr)` columns
- **Activities Grid**: Responsive grid with `minmax(200px, 1fr)` columns
- **Gutters**: Consistent `2rem` gap between grid items

### Spacing

- **Section Padding**: `3rem` top and bottom
- **Card Padding**: `1.5rem` internal padding
- **Margins**:
  - Between sections: `3rem`
  - Between elements: `1rem` to `2rem`

## Navigation

### Desktop (1025px+)

- **Width**: `280px` fixed sidebar
- **Background**: Primary blue
- **Text Color**: White
- **Hover State**: Accent red with underline animation
- **Active State**: Same as hover

### Mobile (≤1024px)

- **Full-screen overlay** with same styling as desktop
- **Toggle Button**: Fixed position in primary blue

## Buttons

### Types

1. **Primary Button**:
   - Background: Accent red
   - Text: White
   - Padding: `0.75rem 1.5rem`
   - Border radius: `4px`

2. **Secondary Button**:
   - Background: Transparent
   - Text: White
   - Border: `2px solid white`

3. **Outline Button**:
   - Background: Transparent
   - Text: Primary blue
   - Border: `2px solid primary blue`

### States

- **Hover**:
  - Slight upward translation (`translateY(-2px)`)
  - Subtle shadow
  - For navigation: color change to accent red

## Cards

### Common Card

- **Background**: White
- **Border radius**: `8px`
- **Shadow**: Subtle (`0 4px 6px rgba(0,0,0,0.1)`)
- **Hover**: Slight lift (`translateY(-5px)`)
- **Image Height**: `200px` with `object-fit: cover`

### Event Card

- **Date Badge**:
  - Background: Accent red
  - Text: White
  - Fixed width: `60px`

## Forms

### Input Fields

- **Padding**: `0.75rem`
- **Border**: `1px solid #ddd`
- **Border radius**: `4px`
- **Background**: White

### Labels

- **Color**: Dark gray (`#333`)
- **Margin**: `0.5rem` below label

## Sections

### Hero Sections

- **Background**: Primary blue with optional overlay image
- **Text**: White
- **Padding**: `4rem 2rem` (desktop), `2rem 1rem` (mobile)

### Section Headers

- **Text Alignment**: Centered
- **Color**: Primary blue
- **Decoration**: Accent red underline (60px wide, 3px tall)

## Animations & Effects

### Micro-interactions

- **Hover States**: 0.3s ease transitions
- **Button Hover**: Subtle upward movement
- **Link Hover**: Color change to accent red

### Attention Elements

- **Pulse Animation**: Alternates between accent red and orange
- **Duration**: 2s infinite loop

## Responsive Breakpoints

1. **Mobile (default)**: Styles apply to all sizes
2. **Tablet (≥768px)**: Adjustments for medium screens
3. **Desktop (≥1025px)**: Full desktop layout with sidebar

## Accessibility

### Contrast

- Primary blue/white: AAA compliant
- Accent red/white: AAA compliant

### Focus States

- Ensure all interactive elements have visible focus states
- Use the accent color for focus indicators

## Implementation Notes

1. **Use CSS Variables** for all colors to maintain consistency
2. **Component-based Structure**: Reuse card, button, and section styles
3. **Mobile-first Approach**: Build base styles then enhance for larger screens
4. **Consistent Spacing**: Use `rem` units based on `16px` root size

## Example Components

### Button Markup

```html
<a href="#" class="button primary">Primary Action</a>
<a href="#" class="button secondary">Secondary Action</a>
<a href="#" class="button outline">Tertiary Action</a>
```

### Card Markup

```html
<div class="card">
  <img src="image.jpg" class="card-image" alt="...">
  <div class="card-content">
    <h3>Card Title</h3>
    <p>Card description text...</p>
    <a href="#" class="button primary">Learn More</a>
  </div>
</div>
```

### Section Markup

```html
<section class="mission-section">
  <h2>Our Mission</h2>
  <div class="mission-content">
    <!-- Content here -->
  </div>
  <div class="mission-image">
    <img src="image.jpg" alt="...">
  </div>
</section>
```

This guideline ensures visual consistency while maintaining flexibility for different content types across your website.
