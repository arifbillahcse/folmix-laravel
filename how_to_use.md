# Homepage Sections — How to Use (Admin Guide)

এই ফাইলে হোমপেজের সব কাস্টম/ডাইনামিক সেকশন কীভাবে অ্যাডমিন প্যানেল থেকে কন্ট্রোল করবেন তার
স্টেপ-বাই-স্টেপ ইনস্ট্রাকশন দেওয়া আছে।

**সাধারণ নিয়ম:** সব জায়গায় যেতে হবে —
`Admin Panel → Settings → Themes → Create Theme` (অথবা আগে থাকলে সেই থিমটা Edit করুন)

প্রতিটা সেকশনের জন্য নিচে দেওয়া **Type** এবং **Name** (হুবহু, একই বানানে/কেসে) সেট করতে হবে —
কারণ হোমপেজের কোড এই নাম দিয়েই সেকশন খুঁজে বের করে।

---

## 1. Hero Slider (বাম পাশের ঘোরানো স্লাইডার)

- **Type:** Image Carousel
- **Name:** `Hero Slider`
- যতগুলো ইমেজ যোগ করবেন সবগুলোই স্লাইড হয়ে ঘুরবে (auto-rotate)।
- প্রতিটা ইমেজের সাথে **Link** দিতে পারবেন — ক্লিক করলে যেখানে যাবে।

## 2. Offer Banner (ডান পাশের স্ট্যাটিক ব্যানার)

- **Type:** Image Carousel
- **Name:** `Offer Banner`
- একাধিক ইমেজ আপলোড করলেও শুধু **প্রথম ইমেজ**টাই দেখাবে (static, ঘুরবে না)।
- ইমেজের সাথে Link সেট করা যাবে।

## 3. Brands (ব্র্যান্ড স্ট্রিপ / লোগো সারি)

- **Type:** Image Carousel
- **Name:** `Brands`
- এখানে প্রতিটা ইমেজ একটা ব্র্যান্ড লোগো হিসেবে দেখাবে (Nike, Adidas ইত্যাদি স্টাইলে)।
- প্রতিটা লোগোর Link দিলে ক্লিক করে ওই ব্র্যান্ডের পেজে যাবে।
- **সেকশনের হেডিং (অপশনাল):** একটা আলাদা থিম কাস্টমাইজেশন তৈরি করুন —
  **Type:** Footer Links, **Name:** `Brand Strip Heading`। এখানে একটা লিংক অ্যাড করে
  তার **Title** এ যা লিখবেন সেটাই হেডিং হিসেবে দেখাবে (URL ফাঁকা রাখলেও চলবে)।
  এই এন্ট্রি না থাকলে ডিফল্ট হিসেবে "Shop by Brand" দেখাবে।

## 3a. Shop by Category হেডিং (অপশনাল)

- ক্যাটাগরি সেকশনের হেডিং টেক্সট পরিবর্তন করতে চাইলে —
  **Type:** Footer Links, **Name:** `Category Section Heading`। এখানে একটা লিংক অ্যাড করে
  তার **Title** এ যা লিখবেন সেটাই হেডিং হিসেবে দেখাবে (URL ফাঁকা রাখলেও চলবে)।
  এই এন্ট্রি না থাকলে ডিফল্ট হিসেবে "Shop by Category" দেখাবে।

## 4. Category Banner (ক্যাটাগরি সেকশনের নিচের বড় ব্যানার)

- **Type:** Image Carousel
- **Name:** `Category Banner`
- "Shop by Category" সেকশনের ঠিক নিচে, "Our Popular Products" এর আগে দেখাবে।
- একটাই বড়, ফুল-উইড্থ ব্যানার — একাধিক ইমেজ দিলেও শুধু **প্রথম ইমেজ**টাই ব্যবহার হবে।
- ইমেজের সাথে Link সেট করলে ক্লিক করলে সেখানে যাবে।
- কোনো ইমেজ না থাকলে এই সেকশনটা এমনিতেই হাইড থাকবে (এরর দেখাবে না)।

## 5. Flash Sale (হাতে বাছাই করা প্রোডাক্ট নিয়ে সেল সেকশন)

- **Type:** Flash Sale
- **Name:** যেকোনো নাম দেওয়া যাবে (এটা স্ট্রিক্ট না, কারণ টাইপ দিয়েই খোঁজা হয়)
- ফর্মে যা পূরণ করবেন:
  - **Title** — সেকশনের হেডিং (যেমন: "Flash Sale")
  - **Subtitle** — ছোট সাবটাইটেল (অপশনাল)
  - **View All URL** — "View All" বাটনের লিংক (অপশনাল)
  - **Add Product** বাটনে ক্লিক করে প্রোডাক্ট সার্চ করে যোগ করুন
  - প্রোডাক্ট লিস্টে ড্র্যাগ করে (⋮⋮ আইকন ধরে) অর্ডার সাজাতে পারবেন — যে অর্ডারে
    সাজাবেন, ফ্রন্টএন্ডে ঠিক সেই অর্ডারেই দেখাবে
  - প্রোডাক্ট রিমুভ করতে চাইলে ডানপাশের "Delete" এ ক্লিক করুন

## 5a. Promo Banner (হেডিং + একাধিক প্রোমো কার্ড সেকশন)

- **Type:** Promo Banner
- **Name:** যেকোনো নাম দেওয়া যাবে
- **Heading Block** এ যা পূরণ করবেন:
  - **Heading** — বড় হেডিং টেক্সট (আবশ্যক)
  - **Text** — সাবটেক্সট (অপশনাল)
  - **Button Text** / **URL** — হেডিং এর নিচে বাটন দিতে চাইলে (অপশনাল)
- **Promo Cards** এ "Add Card" ক্লিক করে যতগুলো ইচ্ছা কার্ড যোগ করতে পারবেন:
  - **Card Title** (আবশ্যক), **Text**, **Card Image** (অপশনাল), **Button Text** / **URL** (অপশনাল)
  - কার্ড লিস্টে ড্র্যাগ করে (⋮⋮ আইকন ধরে) অর্ডার সাজানো যাবে
  - কার্ডে ইমেজ না দিলে শুধু টেক্সট/টাইটেল/বাটন দেখাবে
  - "Delete" এ ক্লিক করে কার্ড রিমুভ করা যায়

## 5b. WhatsApp / Messenger চ্যাট বাটন (সব পেজে ফ্লোটিং বাটন)

- **Type:** Footer Links
- **Name:** `Chat Widgets` (হুবহু এই নামেই হতে হবে)
- এখানে যতগুলো লিংক লাগবে অ্যাড করুন —
  - **WhatsApp** এর জন্য: Title এ লিখুন `WhatsApp`, URL এ দিন `https://wa.me/<দেশের কোড><নম্বর>`
    (যেমন: `https://wa.me/8801779440297`)
  - **Messenger** এর জন্য: Title এ লিখুন `Messenger`, URL এ দিন `https://m.me/<আপনার পেজের ইউজারনেম>`
- দুটোই দিলে দুটো বাটনই দেখাবে, একটা দিলে একটাই দেখাবে। কোনোটা না দিলে সেকশনটাই হাইড থাকবে।
- বাটন দুটো ওয়েবসাইটের **সব পেজেই** নিচের-ডান কোণায় (ফ্লোটিং) সবসময় দেখাবে।

## 6. Just for You (পার্সোনালাইজড প্রোডাক্ট গ্রিড সেকশন)

- **Type:** কোনো টাইপ সেটিং লাগে না — এটা automatically হোমপেজে আসে
- **দেখাবে:** 3 rows × 4 columns = 12 টি প্রোডাক্ট (Daraz-এর মতো personalized section)
- **কাজ করে এভাবে:**
  - **Returning Customers:** যারা এর আগে কোনো প্রোডাক্ট দেখেছে, তাদের সেই ক্যাটাগরি থেকে **featured products** দেখাবে
  - **New Visitors:** যারা প্রথমবার আসলো বা কোনো প্রোডাক্ট দেখেনি, তাদের সব থেকে **featured products** দেখাবে
- **কোনো অ্যাডমিন সেটিংস লাগে না** — products যা "featured" হিসেবে মার্ক করা আছে (Catalog → Products এ edit করে Featured checkbox check করলে) সেগুলোই এতে দেখাবে
- **কাস্টমার প্রাইভেসি:** শুধু browser-এর cookie-তে সংরক্ষিত হয় (server এ save হয় না), এবং ৩০ দিন পর expire হয়ে যায়

### Just for You কীভাবে কাজ করে (Technical)

- যখন কেউ একটা প্রোডাক্ট পেজ ভিজিট করে → ব্রাউজার একটা cookie-তে সেই প্রোডাক্টের প্রথম category ID store করে রাখে
- পরবর্তী বার যখন homepage load হয় → সেই category-র featured products দেখায়
- যদি সেই category-তে কোনো featured product না থাকে → সব থেকে featured products দেখায় (global fallback)

## 7. Footer Links

- **Type:** Footer Links
- **Name:** যেকোনো নাম
- ফুটারে "Quick Links" কলামের নিচে যেসব লিংক দেখাবে সেগুলো এখান থেকে অ্যাড/এডিট করা যায়।

## 8. Top Bar (Contact / Links) — হেডারের উপরের কমলা বার

- Top Bar এর কন্ট্যাক্ট নাম্বার, ইমেইল এবং লিংকগুলো থিম কাস্টমাইজেশন থেকেই কন্ট্রোল হয়
  (Static Content টাইপ ব্যবহার করা হয়েছে — এডিট করতে হলে থিম লিস্টে "Top Bar Contact" ও
  "Top Bar Links" নামের এন্ট্রি দুটো খুঁজে Edit করুন)।

---

## নতুন ইমেজ/ব্যানার যোগ করলে ফ্রন্টএন্ডে না দেখালে করণীয়

সার্ভারে নতুন কোড ডিপ্লয় করার পর (অথবা কনফিগ/কোড পরিবর্তনের পর) ক্যাশ ক্লিয়ার করতে হবে:

```bash
php artisan view:clear
php artisan optimize:clear
```

## নতুন ছবি আপলোডের পর ছবি 403 / ব্রোকেন দেখালে

cPanel-এ LiteSpeed symlink protection এর কারণে `storage:link` কাজ করে না, তাই আমরা rsync cron
দিয়ে `storage/app/public/` থেকে `public/storage/` এ ফাইল সিঙ্ক করি। এই cron প্রতি
15–30 মিনিট পরপর চলে, তাই নতুন ইমেজ আপলোডের পর একটু সময় লাগতে পারে। জরুরি হলে ম্যানুয়ালি চালান:

```bash
cp -r /disk2/demosoftorio/laravel.demo.softorio.com/storage/app/public/* /disk2/demosoftorio/laravel.demo.softorio.com/public/storage/
```

অথবা rsync দিয়ে (আরও efficient):

```bash
rsync -a /disk2/demosoftorio/laravel.demo.softorio.com/storage/app/public/ /disk2/demosoftorio/laravel.demo.softorio.com/public/storage/
```

**নোট:** সার্ভারের actual পাথ অনুযায়ী `/disk2/demosoftorio/laravel.demo.softorio.com` প্রতিস্থাপন করুন।

---

## Core-safe কাস্টমাইজেশন (ভবিষ্যতে আপগ্রেডের জন্য)

Flash Sale API (`/api/products/flash-sale`) আগে core `Shop` প্যাকেজে সরাসরি যোগ করা হয়েছিল, যেটা
ভবিষ্যতে Bagisto আপগ্রেড করলে merge conflict করত। এখন এটা একটা আলাদা প্যাকেজে সরানো হয়েছে —

`packages/Webkul/ShopExtension/`

এই প্যাকেজে আমাদের নিজস্ব কাস্টম রুট/কন্ট্রোলার থাকবে যেগুলো core Bagisto ফাইল স্পর্শ না করেই
কাজ করে। ভবিষ্যতে নতুন কাস্টম ফিচার (custom API endpoint, custom controller ইত্যাদি) লাগলে এই
প্যাকেজেই যোগ করা উচিত, যাতে `packages/Webkul/Shop/`, `packages/Webkul/Admin/` ইত্যাদি core
প্যাকেজ অপরিবর্তিত থাকে এবং Bagisto আপগ্রেড করার সময় merge conflict না হয়।

**নিয়ম:** কখনো `packages/Webkul/<CorePackage>/` এর ভেতরের ফাইল সরাসরি এডিট করবেন না। প্রয়োজনে
`ShopExtension` প্যাকেজে নতুন রুট/কন্ট্রোলার/লিসেনার যোগ করুন।
