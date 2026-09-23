# AutoPartFlow UI Style Guide

This is the visual and interaction standard for every AutoPartFlow screen. The sales representative workspace is the reference implementation. New work should extend this system instead of introducing a new theme.

## 1. Brand foundation

### Logo

Use the same asset everywhere:

`public/assets/images/logo-icon.png`

- Standard display size: **40 × 40px**.
- Compact mobile header: **32 × 32px**.
- Authentication emphasis: up to **56 × 56px**.
- Preserve aspect ratio with `object-fit: contain`.
- Use a white background, 3px internal padding, and 9px radius when the logo sits on navy.
- The image `alt` is `AutoPartFlow` when it is the only brand label. Use an empty `alt` when visible brand text is beside it.
- Do not replace it with initials, a gear icon, a car icon, emoji, or another logo file.

### Color source

The canonical variables live in `public/assets/css/sales-rep/tokens.css`. Shared public/admin aliases and global behavior live in `public/assets/css/shared.css`.

| Purpose | Token | Value |
|---|---|---|
| Main navy | `--sales-primary` | `#002045` |
| Navy hover/selected | `--sales-primary-container` | `#1a365d` |
| Supporting blue | `--sales-secondary` | `#3b6090` |
| Page background | `--sales-surface` | `#f9f9ff` |
| Card background | `--sales-surface-lowest` | `#ffffff` |
| Soft section background | `--sales-surface-low` | `#f0f3ff` |
| Main text | `--sales-on-surface` | `#121c2c` |
| Muted text | `--sales-on-surface-variant` | `#43474e` |
| Border | `--sales-outline-variant` | `#c4c6cf` |
| Success | `--sales-success` | `#146c43` |
| Warning | `--sales-warning` | `#875500` |
| Error/destructive | `--sales-error` | `#ba1a1a` |
| Information | `--sales-info` | `#1e4f91` |

Primary navy is for navigation, headings, primary buttons, selected controls, and chart series. Status colors communicate state only; do not use them as decoration.

Do not add gradients to cards, buttons, headings, or backgrounds. Prefer flat surfaces, borders, and subtle shadows.

## 2. Typography

Use `Inter, "Segoe UI", Roboto, Arial, sans-serif` everywhere. Use `JetBrains Mono` only for identifiers such as order numbers, SKUs, invoice numbers, and codes.

| Element | Size | Weight | Guidance |
|---|---:|---:|---|
| Page title / `h1` | 28px desktop, 24–26px mobile | 700 | One per screen; short and specific |
| Section title / `h2` | 18–20px | 700 | Describe the card or section |
| Card title / `h3` | 15–18px | 600–700 | Avoid title case inconsistency |
| Body | 14px | 400 | Default interface copy |
| Supporting text | 12–13px | 400–500 | Use muted color |
| Eyebrow/label | 11–12px | 700 | Uppercase with restrained letter spacing |
| Button | 14px | 600 | Use verbs: “Save Customer”, “Add Stock” |

Avoid oversized marketing headings, gradient text, vague slogans, and long paragraphs. Customer pages should explain useful actions plainly.

## 3. Spacing, shape, and elevation

- Use spacing in multiples of **4px**: 4, 8, 12, 16, 20, 24, 32, 48.
- Default page padding: **28px desktop**, **16–20px mobile**.
- Card padding: **16–24px** depending on density.
- Component gap: **8–12px**; section gap: **16–24px**.
- Small radius: **6px**; controls: **9px**; cards/dialogs: **12px**.
- Use `--sales-shadow` for normal cards and `--sales-shadow-overlay` for dialogs/dropdowns.
- Do not create deeply layered cards or use large decorative shadows.

## 4. Shared components

### Buttons

- Minimum height: **42px**, preferably **44px** for primary/mobile actions.
- Primary: navy background, white text.
- Secondary: white/soft background, navy text, visible border.
- Destructive: error red and explicit wording such as “Delete Order”.
- Icon buttons need an accessible label and a consistent 40–44px target.
- Disable unavailable actions visibly. Never make a dead button look active.

Prefer the existing classes: `sales-button`, `sales-button--primary`, `sales-button--secondary`, `sales-button--danger`, `sales-button--compact`, and `sales-button--full`.

### Cards

Use `sales-card` where the sales component styles are loaded. Cards use a white surface, 12px radius, a subtle border/shadow, and a clear title area. Do not mix card radii or shadows on the same page.

### Forms

- Inputs/selects: at least **42px** high, 9px radius, white background, token border.
- Every field needs a visible label; placeholders are examples, not labels.
- Show validation beside the related field and use error color only for actual errors.
- Keep field order aligned with the task. Put the main submit action last and on the right on desktop.
- Never prefill real-looking credentials or business data unless it is genuine application data.

### Status badges

Use consistent meanings:

- Pending / warning: amber.
- Processing / information: blue.
- Delivered / active / in stock: green.
- Cancelled / failed / low stock / destructive: red.
- Neutral/draft: muted surface and text.

Do not communicate status by color alone; always include a text label.

### Icons

Use the existing inline SVG sprite for staff workspaces or Material Symbols on public screens. Standard icon size is **20–22px**. Use one icon family within a navigation area. Do not use emoji as controls or status icons.

### Tables

- Left-align text; right-align money and quantities.
- Use 12px uppercase column labels and 12–16px cell padding.
- Keep row states subtle. Use badges for status.
- Put wide tables in an `overflow-x: auto` wrapper; the page itself must not scroll horizontally.
- Provide a useful empty state instead of an empty table body.

### Dialogs and notifications

- Use the native `dialog` element with `sales-dialog` when possible.
- Every dialog needs a labelled title, close button, Cancel action, and explicit primary/destructive action.
- Keep dialogs within the viewport and stack action buttons on small screens.
- Toasts confirm reversible interface actions. Persistent failures belong near the affected content.

### Charts

- First series: `--sales-primary`; second series: `--sales-secondary`.
- Use status colors only for thresholds or meaning.
- Keep gridlines light, labels readable, and backgrounds plain.
- Charts need a title and understandable labels. Avoid 3D, heavy gradients, and decorative animation.

## 5. Navigation by role

Each role has one clear shell and only sees destinations relevant to that role.

### Customer/public

Header destinations: **Home, Catalog, Track Order, Cart, Sign In**. Do not expose staff dashboards, POS, inventory, reports, or admin tools. Only link to routes that exist.

### Sales representative

Sidebar/mobile destinations: **Dashboard, Point of Sale, Orders, Customers**. Use “Sales Workspace” language and the SR profile identifier until real user data is connected.

### Store manager

Show inventory/procurement destinations only as they are implemented. The current implemented destination is **Inventory**. Do not copy sales links into this role.

### Administrator/business owner

Destinations: **Dashboard, Reports, User Management, Employees, Notifications, Settings**. Keep the same order and labels on every admin screen.

Active navigation must use `aria-current="page"` and the navy selected style. A role-specific menu is presentation; backend authorization must still protect the route.

## 6. Page composition

Staff screen order:

1. Shared role header/sidebar.
2. Eyebrow identifying the workspace.
3. One page title and a one-line description.
4. Primary action on the right.
5. Summary cards only when values are meaningful.
6. Search/filter toolbar.
7. Main table, form, or workflow.
8. Empty/error/loading state.

Customer screen order:

1. Shared public header.
2. Clear task-focused heading.
3. One primary customer action and one optional secondary action.
4. Useful catalog/order content.
5. Shared footer.

Avoid “AI-generated” visual patterns: giant gradients, floating glass cards, fake browser chrome, generic feature grids, random colored icons, excessive rounded pills, invented metrics, and promotional copy for unfinished features.

## 7. Responsive behavior

Required checks: desktop and **390px-wide phone**.

- Staff sidebar width is 256px on desktop; use the established off-canvas/mobile navigation at 900px or below.
- Public navigation may wrap or scroll; it must remain readable without clipping words.
- KPI grids reduce from four columns to two, then one where needed.
- Forms collapse to one column.
- Primary mobile actions may become full width.
- Tables scroll inside their card.
- Dialogs fit the viewport with internal scrolling.
- No page may create horizontal viewport overflow.
- Keep tap targets at least 42px high and avoid controls that depend on hover.

## 8. Accessibility and interaction

- Use semantic headings in order and one `h1` per screen.
- Add visible keyboard focus using the shared focus color.
- Give icon-only buttons an `aria-label`.
- Associate labels with fields and errors with their controls.
- Mark active navigation with `aria-current="page"`.
- Label dialogs with `aria-labelledby`.
- Maintain readable contrast; never place muted blue text on navy.
- Respect `prefers-reduced-motion`.
- Provide honest loading, empty, success, and error states.
- Do not hide unfinished behavior behind an active-looking control. Disable it or label it clearly.

## 9. File ownership

- Shared design tokens: `public/assets/css/sales-rep/tokens.css`.
- Cross-project mappings and global accessibility: `public/assets/css/shared.css`.
- Admin presentation: `public/assets/css/admin.css`.
- Public presentation: `public/assets/css/public/`.
- Inventory presentation: `public/assets/css/inventory/`.
- Sales shell/components/screens: `public/assets/css/sales-rep/`.
- Role layouts: `app/Views/layouts/`.

Keep screen-specific styles in the matching module. Do not put sales screen styles in `style.css`, and do not duplicate tokens in individual views. Inline styles should be removed when editing a screen and moved to its module stylesheet.

## 10. UI change checklist

Before considering a UI change complete:

- [ ] Uses the standard logo and Inter font.
- [ ] Uses existing tokens; no new independent palette or gradient.
- [ ] Reuses shared button, card, badge, form, dialog, icon, and chart patterns.
- [ ] Shows the correct navigation for the user role.
- [ ] Links only to existing routes and does not expose staff pages publicly.
- [ ] Has one clear page title and honest interface copy.
- [ ] Has labels, focus states, active navigation, and accessible icon buttons.
- [ ] Handles empty, error, and disabled states.
- [ ] Works at desktop and 390px without viewport overflow.
- [ ] Keeps tables/dialogs internally scrollable on small screens.
- [ ] Loads the logo and all referenced assets.
- [ ] Passes PHP and JavaScript syntax checks.
- [ ] Does not change backend behavior during a UI-only task.

