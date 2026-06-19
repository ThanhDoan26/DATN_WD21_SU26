# 🎨 CineTicket Design System

## Overview
Giao diện đăng ký/đăng nhập chuyên nghiệp với design system hiện đại theo tiêu chuẩn enterprise.

## 📐 Architecture

### Color Palette
- **Primary**: Purple (#9333ea - #581c87)
- **Brand**: Red/Pink (#ff4d7d - #8b0a28)
- **Neutral**: Gray Scale (#0f172a - #f8fafc)
- **State Colors**:
  - Success: Emerald (#10b981)
  - Error: Red (#ef4444)
  - Warning: Amber (#f59e0b)
  - Info: Blue (#3b82f6)

### Typography
- **Font Family**: Inter (body) + Sora (headings)
- **Sizes**:
  - h1: 2.25rem (36px) - semibold
  - h2: 1.875rem (30px) - semibold
  - Base: 1rem (16px)
  - Small: 0.875rem (14px)
  - Tiny: 0.75rem (12px)

### Spacing
- Base unit: 0.25rem (4px)
- Scales: 1x, 2x, 3x, 4x, 6x, 8x, 12x, 16x...

### Shadows & Depth
```css
/* Card shadow */
box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);

/* Glow effect */
box-shadow: 0 0 30px rgba(168, 85, 247, 0.3);
```

## 🧩 Components

### Form Input
```blade
<x-form.input
    label="Địa chỉ Email"
    type="email"
    name="email"
    placeholder="you@example.com"
    hint="Sử dụng email hợp lệ"
    required
/>
```

### Form Checkbox
```blade
<x-form.checkbox
    label="Tôi đồng ý với Điều khoản"
    name="agree"
    required
/>
```

### Buttons
```blade
<!-- Primary Button -->
<x-button.primary type="submit">
    Đăng Nhập
</x-button.primary>

<!-- Secondary Button -->
<x-button.secondary>
    Hủy bỏ
</x-button.secondary>
```

### Alert
```blade
<!-- Success Alert -->
<x-alert type="success" title="Thành công!">
    Thao tác của bạn đã hoàn thành
</x-alert>

<!-- Error Alert -->
<x-alert type="error" title="Lỗi" dismissible>
    Có lỗi xảy ra, vui lòng thử lại
</x-alert>
```

## 🎨 CSS Classes

### Form Elements
- `form-group`: Container cho form field
- `form-label`: Label styling
- `form-input`: Input field styling
- `form-error`: Error message styling
- `form-hint`: Helper text styling
- `form-input-icon`: Icon inside input

### Buttons
- `btn`: Base button class
- `btn-primary`: Primary button variant
- `btn-secondary`: Secondary button variant
- `btn-tertiary`: Tertiary button variant
- `btn-block`: Full width button
- `btn-lg`: Large button
- `btn-sm`: Small button
- `btn-loading`: Loading state

### Alerts
- `alert`: Base alert class
- `alert-success`: Success styling
- `alert-error`: Error styling
- `alert-warning`: Warning styling
- `alert-info`: Info styling

### Layout
- `auth-container`: Authentication page container
- `auth-card`: Auth form card
- `auth-card-content`: Card content padding
- `auth-header`: Header section
- `auth-title`: Main title
- `auth-subtitle`: Subtitle text

## ✨ Animations

### Keyframes
- `fadeIn`: Fade in animation (0.5s)
- `slideUp`: Slide up animation (0.6s)
- `slideDown`: Slide down animation (0.6s)
- `pulse`: Pulse animation (built-in)

### Usage
```html
<!-- Fade in -->
<div class="animate-fade-in">Content</div>

<!-- Slide up -->
<div class="animate-slide-up">Content</div>

<!-- Slow pulse -->
<div class="animate-pulse-slow">Content</div>
```

## 🌈 States & Interactions

### Hover States
```css
/* Button hover */
.btn-primary:hover {
    box-shadow: 0 20px 25px -5px rgba(168, 85, 247, 0.3);
}

/* Input hover */
.form-input:hover {
    border-color: rgb(75, 85, 99);
}

/* Link hover */
.link-primary:hover {
    color: rgb(196, 181, 253);
}
```

### Focus States
```css
.form-input:focus {
    ring: 2px rgba(168, 85, 247, 1);
    background-color: rgba(31, 41, 55, 0.8);
}
```

### Active States
```css
.btn-primary:active {
    transform: scale(0.95);
}
```

## 📱 Responsive Design

### Breakpoints
- `sm`: 640px
- `md`: 768px
- `lg`: 1024px
- `xl`: 1280px
- `2xl`: 1536px

### Mobile-First Approach
```html
<!-- Small on mobile, larger on desktop -->
<div class="text-lg sm:text-2xl md:text-3xl">Responsive text</div>

<!-- Hidden on mobile, visible on desktop -->
<div class="hidden md:block">Desktop only</div>
```

## ♿ Accessibility

### ARIA Attributes
- `aria-label`: Describe interactive elements
- `aria-required`: Mark required fields
- `aria-invalid`: Mark invalid fields
- `aria-describedby`: Link help text to input

### Semantic HTML
```blade
<!-- Use semantic elements -->
<form method="POST">
    <fieldset>
        <legend>Đăng Nhập</legend>
        <!-- form fields -->
    </fieldset>
</form>
```

### Keyboard Navigation
- Tab order: Logical flow
- Focus indicators: Visible (ring-2)
- Skip links: Jump to main content

## 🔒 Security

### CSRF Protection
```blade
@csrf
```

### Input Validation
- Email validation: Built-in HTML5
- Password strength: Client + Server side
- Rate limiting: Server-side

## 📊 Performance

### Optimization
- CSS: Tailwind JIT (no unused CSS)
- Icons: Inline SVG (minimal requests)
- Images: Lazy loading (where applicable)
- Fonts: Google Fonts with preconnect

### Lighthouse Metrics
- Performance: > 90
- Accessibility: 100
- Best Practices: 100
- SEO: 100

## 🛠️ Customization

### Change Primary Color
1. Edit `tailwind.config.js` - `colors.primary`
2. Update CSS variables in `app.css`
3. Regenerate CSS

### Add New Component
1. Create file: `resources/views/components/[name].blade.php`
2. Use `@props()` for attributes
3. Apply CSS classes from design system

### Add New Animation
1. Add keyframes in `tailwind.config.js` - `keyframes`
2. Add animation name in `animation`
3. Use with `animate-[name]` class

## 📚 Resources

- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Inter Font](https://rsms.me/inter/)
- [Sora Font](https://fonts.google.com/specimen/Sora)
- [Accessibility Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

## 🚀 Best Practices

1. **Consistency**: Use design system components, don't create custom styles
2. **Responsiveness**: Always test on mobile, tablet, desktop
3. **Accessibility**: Use semantic HTML, ARIA attributes
4. **Performance**: Minimize requests, optimize images
5. **Maintainability**: Keep components simple and focused
6. **Documentation**: Comment complex components

## 📝 Version History

- v1.0.0 (2024-06-04)
  - Initial design system
  - Authentication pages
  - Component library
  - Design documentation
