# "Just for You" Personalized Products Section - Implementation Guide

## Overview

A Daraz-style "Just for You" personalized product grid section has been added to the homepage. It displays **3 rows × 4 columns (12 featured products)** tailored to each visitor based on their browsing history.

## How It Works

### For Returning Customers
When a customer visits a product page:
1. **Browser stores category ID** in a cookie (`last_viewed_category_id`)
2. Cookie expires in **30 days** (rolling window)
3. On next homepage visit → displays **featured products from that category**

### For New Visitors
- **No history cookie** → shows **featured products globally** (fallback)
- Smooth experience, no empty sections

### Data Privacy
- History stored **only in browser cookie**, never sent to server database
- No personal tracking, no server-side user profiling
- Privacy-friendly personalization

---

## Architecture

### Files & Components

#### 1. **ShopExtension Package** (Core Logic)
```
packages/Webkul/ShopExtension/
├── src/Http/Controllers/API/JustForYouController.php
│   └── GET /api/products/just-for-you
│       Fetches featured products by category_id (or global fallback)
│
├── src/Http/View/Composers/JustForYouComposer.php
│   └── Registered as view composer for shop::home.index
│       Reads cookie, queries products, binds $justForYouProducts
│
├── src/Providers/ShopExtensionServiceProvider.php
│   └── Registers view composer + loads routes
│
└── src/Routes/api.php
    └── Adds /api/products/just-for-you route
```

#### 2. **Theme Overrides**
```
resources/themes/default/views/

├── home/index.blade.php
│   └── Replaces "Product on Sale!" carousel with <x-shopext::just-for-you-grid>
│
└── vendor/shop/products/view.blade.php
    └── Theme override of product detail page
        Injects inline script to store category ID in cookie
```

#### 3. **Blade Component**
```
packages/Webkul/ShopExtension/src/Resources/views/
└── components/just-for-you-grid.blade.php
    └── Renders 3×4 grid using reusable x-shop::products.card
        Props: title, navigationLink, products
        Button text: "View More"
```

---

## Server-Side (Idea 2) vs Client-Side Approaches

**Why Idea 2 (Server-Rendered) was chosen:**
- ✅ No layout shift — products render on initial page load
- ✅ SEO-friendly (products indexed by search engines)
- ✅ Works even with JavaScript disabled
- ✅ Matches Bagisto's native pattern ("New Arrival", "Flash Sale")
- ✅ Uses existing ProductRepository (no new queries)
- ❌ Updates only on next page load (not real-time)

**vs Idea 1 (Client-Side Vue):**
- ✅ Real-time updates without page reload
- ❌ Products "pop in" after page load (layout shift)
- ❌ Requires extra API call per page load
- ❌ Harder for SEO indexing

---

## Admin Control

**No theme customization needed** for "Just for You" section.

### What Admins Control
Products shown are determined by their **"Featured" status**:

1. **Mark product as featured:**
   - Admin → Catalog → Products → [select product] → Edit
   - Check **"Featured"** checkbox (in General tab)
   - Save

2. **"Just for You" automatically shows:**
   - Featured products from customer's last-viewed category (if exists)
   - Featured products globally (if no history or no products in category)

### No Configuration Needed
- Section appears automatically on homepage
- Heading is hardcoded as "Just for You"
- Styling: 3×4 grid on desktop, 3 cols on tablet, 2 cols on mobile

---

## Database / Dependencies

### Repositories Used
- `ProductRepository::where('featured', 1)` (existing method)
- `ProductRepository::whereHas('categories', ...)` (existing method)

### No Migrations
- No new tables
- Uses existing `products.featured` column
- Uses existing `category_product` pivot table

### Laravel Features Used
- View Composers (`View::composer()`)
- Route registration
- Cookie handling (Laravel's built-in)

---

## Testing the Feature

### Setup
1. Mark at least 12 products as "Featured" (Catalog → Products → Featured checkbox)
2. Ensure products belong to various categories

### Test Scenario 1: New Visitor
1. Open homepage (no history cookie)
2. Should see 12 featured products (global list)

### Test Scenario 2: Returning Visitor
1. Visit a product page (e.g., category "Electronics")
2. Go back to homepage
3. Should see featured products from "Electronics" category
4. If less than 12, fills with global featured (fallback)

### Test Scenario 3: Cookie Expiry
1. Visit product page (sets cookie)
2. Wait 30+ days
3. Cookie expires, back to global featured list

---

## Troubleshooting

### "Just for You" section not showing
**Cause:** No featured products
**Fix:** Mark products as featured (Catalog → Products → Featured)

### Products not from expected category
**Cause:** Fewer than 12 featured products in that category
**Fix:** Normal! Component shows all from that category, then adds global featured to reach 12

### Cookie not persisting
**Cause:** Browser privacy settings or incognito mode
**Fix:** Normal behavior. Incognito/private browsing doesn't persist cookies

### Grid layout broken
**Cause:** Custom theme CSS override
**Fix:** Ensure Tailwind grid classes aren't overridden:
```css
.grid { display: grid !important; }
.grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
```

---

## File Locations Summary

| File | Purpose |
|------|---------|
| `packages/Webkul/ShopExtension/src/Http/Controllers/API/JustForYouController.php` | API endpoint |
| `packages/Webkul/ShopExtension/src/Http/View/Composers/JustForYouComposer.php` | Fetches & binds products |
| `packages/Webkul/ShopExtension/src/Resources/views/components/just-for-you-grid.blade.php` | Blade component (3×4 grid) |
| `packages/Webkul/ShopExtension/src/Routes/api.php` | API route |
| `packages/Webkul/ShopExtension/src/Providers/ShopExtensionServiceProvider.php` | Service provider |
| `resources/themes/default/views/home/index.blade.php` | Homepage integration |
| `resources/themes/default/views/vendor/shop/products/view.blade.php` | Cookie-writing script |

---

## Maintenance & Future Enhancements

### Current Limitations
- Category-based only (not product-based recommendations)
- Binary featured flag (not ML-driven scoring)
- 30-day cookie window fixed

### Possible Future Enhancements
1. **Multi-category tracking** — remember multiple viewed categories, show variety
2. **Purchase history** — for logged-in customers, show related to purchases
3. **View count weighting** — track how many times each category was viewed
4. **Wishlist integration** — show products similar to wishlist items
5. **Server-side analytics** — optional: track anonymous aggregated trends

### Upgrade Safety
- All code in **ShopExtension package** (not core)
- Theme overrides only (theme layer)
- No migrations or core file changes
- Safe to upgrade Bagisto without conflicts

---

## Git Commits

```
d64dcc7 feat: add "Just for You" personalized grid section on homepage
4b8b4b9 docs: add "Just for You" personalized section documentation to admin guide
```

---

## Questions?

Refer to:
- `/home/user/bagisto/how_to_use.md` — Admin guide (includes "Just for You" section explanation)
- `/home/user/bagisto/JUST_FOR_YOU_IMPLEMENTATION.md` — This file
