# Product Import Guide (Admin → Settings → Data Transfer → Imports)

এই গাইডে বলা আছে কীভাবে CSV ফাইল দিয়ে বাল্ক প্রোডাক্ট ইম্পোর্ট করবেন — কোন কলাম বাধ্যতামূলক,
কোনগুলো ঐচ্ছিক, আর মাল্টি-ভ্যালু ফিল্ডগুলো (ক্যাটাগরি, ইমেজ, ইনভেন্টরি ইত্যাদি) কীভাবে ফরম্যাট
করতে হবে।

দুটো টেমপ্লেট এই রিপোতে দেওয়া আছে:

- **`storage/app/public/data-transfer/samples/csv/products.csv`** — Bagisto-র নিজস্ব অফিসিয়াল
  স্যাম্পল (Admin প্যানেলের "Download Sample" বাটনেও এটাই ডাউনলোড হয়)। এতে simple, configurable,
  grouped, bundle এবং booking — সব ধরনের প্রোডাক্টের রিয়েল এক্সাম্পল আছে, কিন্তু সাইজে বড় এবং
  জটিল।
- **`product_import_demo.csv`** (এই ফাইলের পাশেই আছে) — মাত্র ৩টা সহজ simple প্রোডাক্ট নিয়ে একটা
  ছোট, দ্রুত টেস্ট করার মতো টেমপ্লেট। `images` এবং `categories` কলাম ইচ্ছাকৃতভাবে ফাঁকা রাখা
  হয়েছে (আপনার সাইটে হয়তো সেই নামে ক্যাটাগরি নেই, তাই ফাঁকা রাখলে সেফ থাকবে) — চাইলে আপনার
  সাইটের একটা existing ক্যাটাগরির নাম বসিয়ে দিতে পারেন, এবং প্রথমবার ছবি ছাড়াই ইম্পোর্ট টেস্ট
  করতে পারেন।

---

## ধাপে ধাপে ইম্পোর্ট করার নিয়ম

1. **Admin Panel → Settings → Data Transfer → Imports → Create Import**
2. **Type:** `Products`
3. **File:** আপনার CSV ফাইল আপলোড করুন
4. **Images Directory Path** (ঐচ্ছিক — শুধু ইমেজ কলামে ফাইলনেম দিলে লাগবে):
   - এখানে যা লিখবেন সেটা `storage/app/import/` এর ভেতরের একটা সাব-ফোল্ডারের নাম হতে হবে
   - উদাহরণ: `product-images` লিখলে, ইমেজ ফাইলগুলো
     `storage/app/import/product-images/` ফোল্ডারে রাখতে হবে, এবং CSV-র `images` কলামে শুধু
     ফাইলনেম দিতে হবে (যেমন: `1.webp`), পুরো পাথ না
5. **Settings (ডানপাশে):**
   - **Action:** `Create/Update` (নতুন প্রোডাক্ট যোগ + পুরনোটা আপডেট, দুটোই করবে) অথবা শুধু `Delete`
   - **Validation Strategy:** `Stop on Errors` (কোনো row-এ এরর পেলে পুরো ইম্পোর্ট থামিয়ে দেবে) বা
     `Skip Errors` (এরর row বাদ দিয়ে বাকিগুলো চালিয়ে যাবে)
   - **Allowed Errors:** কতগুলো এরর পর্যন্ত সহ্য করবে (Skip Errors মোডে)
   - **Field Separator:** কমা `,` (ডিফল্ট, না বদলানোই ভালো)
   - **Process In Queue:** চালু রাখুন — বড় ফাইল ব্যাকগ্রাউন্ডে প্রসেস হবে, সাইট স্লো হবে না
     (queue worker চালু থাকতে হবে: `php artisan queue:work`)
6. **Save Import** ক্লিক করুন, তারপর লিস্ট থেকে ইম্পোর্টটা ওপেন করে **Validate Data** →
   এরর না থাকলে **Import** বাটনে ক্লিক করুন

---

## CSV কলামগুলোর বিস্তারিত

| কলাম | বাধ্যতামূলক? | ব্যাখ্যা |
|---|---|---|
| `sku` | **হ্যাঁ** | প্রোডাক্টের ইউনিক কোড। আপডেট করতে চাইলে existing SKU দিন |
| `parent_sku` | Configurable-এর child variant হলে | কোন configurable প্রোডাক্টের variant সেটা বোঝাতে parent-এর SKU |
| `locale` | **হ্যাঁ** | ভাষা কোড, যেমন `en`, `bn` |
| `attribute_family_code` | **হ্যাঁ** | Catalog → Attribute Families এ যে family code সেট করা আছে (সাধারণত `default`) |
| `type` | **হ্যাঁ** | নিচের ৭টার একটা: `simple`, `configurable`, `grouped`, `bundle`, `downloadable`, `virtual`, `booking` |
| `categories` | না | ক্যাটাগরি নাম, একাধিক হলে কমা দিয়ে — `"Men,Winter Wear"`। সাব-ক্যাটাগরি বোঝাতে `/` — `"Men/Winter Wear"` |
| `images` | না | ইমেজ ফাইলনেম, একাধিক হলে কমা দিয়ে — `"1.webp,2.webp"` (উপরে Images Directory Path দেখুন) |
| `name` | **হ্যাঁ** | প্রোডাক্টের নাম |
| `description` / `short_description` | attribute family অনুযায়ী | full ও short বিবরণ |
| `status` | না | `1` = Active, `0` = Disabled |
| `visible_individually` | না | `1` = আলাদাভাবে শপে দেখাবে (configurable-এর child variant গুলোতে সাধারণত `0`) |
| `new` / `featured` | না | `1`/`0` — হোমপেজে "New"/"Featured" ট্যাগ |
| `guest_checkout` | না | `1`/`0` — লগইন ছাড়া কেনা যাবে কিনা |
| `length` / `width` / `height` / `weight` | না | শিপিং হিসেবের জন্য মাপ |
| `tax_category_name` | না | Settings → Taxes এ যে ট্যাক্স ক্যাটাগরি সেট করা আছে |
| `price` | simple/configurable-এর জন্য | বেস প্রাইস (সংখ্যা, যেমন `19.99`) |
| `cost` | না | প্রোডাক্টের cost price |
| `special_price` / `special_price_from` / `special_price_to` | না | ডিসকাউন্ট প্রাইস ও মেয়াদ (তারিখ ফরম্যাট: `YYYY-MM-DD`) |
| `customer_group_prices` | না | কাস্টমার-গ্রুপ ভিত্তিক দাম — নিচে ফরম্যাট দেখুন |
| `url_key` | **হ্যাঁ** | প্রোডাক্ট পেজের URL slug, যেমন `arctic-beanie` |
| `meta_title` / `meta_keywords` / `meta_description` | না | SEO ফিল্ড |
| `manage_stock` | না | `1`/`0` — স্টক ট্র্যাক করবে কিনা |
| `inventories` | manage_stock=1 হলে | কোন সোর্সে কত স্টক — নিচে ফরম্যাট দেখুন |
| `related_skus` / `cross_sell_skus` / `up_sell_skus` | না | রিলেটেড/ক্রস-সেল/আপ-সেল প্রোডাক্টের SKU, কমা দিয়ে আলাদা |
| `configurable_variants` | type=configurable হলে | চাইল্ড variant গুলোর অ্যাট্রিবিউট কম্বিনেশন — নিচে ফরম্যাট দেখুন |
| `bundle_options` | type=bundle হলে | বান্ডেলের অপশনগুলো — নিচে ফরম্যাট দেখুন |
| `associated_skus` | type=grouped হলে | গ্রুপে থাকা প্রোডাক্টগুলো ও কোয়ান্টিটি — নিচে ফরম্যাট দেখুন |
| `booking_options` | type=booking হলে | বুকিং-এর নিয়ম (সময়সূচি, লোকেশন ইত্যাদি) — জটিল, official sample CSV-র শেষের rows গুলো দেখুন |

---

## মাল্টি-ভ্যালু ফিল্ডের ফরম্যাট (key=value, `|` দিয়ে একাধিক এন্ট্রি)

**`inventories`** — কোন ইনভেন্টরি সোর্সে কত স্টক:
```
default=100
```
একাধিক সোর্স হলে: `default=100|warehouse-2=50`

**`customer_group_prices`** — নির্দিষ্ট কাস্টমার গ্রুপ/কোয়ান্টিটির জন্য আলাদা দাম:
```
group=all,qty=2,type=fixed,price=12|group=all,qty=3,type=discount,price=50
```

**`configurable_variants`** (শুধু parent/configurable প্রোডাক্টের row-এ):
```
sku=SP-005,color=Yellow,size=M|sku=SP-006,color=Yellow,size=L
```
প্রতিটা variant-এর জন্য একটা আলাদা `type=simple` row-ও লাগবে, যার `parent_sku` কলামে এই
configurable প্রোডাক্টের SKU বসবে।

**`associated_skus`** (grouped প্রোডাক্টের জন্য) — কোন প্রোডাক্ট, কত কোয়ান্টিটি:
```
SP-001=5,SP-003=5,SP-004=5
```

**`bundle_options`** (bundle প্রোডাক্টের জন্য):
```
name=Bundle Option 1,type=radio,required=1,sku=SP-001,price=15.00,default=0,qty=1|name=Bundle Option 1,type=radio,required=1,sku=SP-002,price=10.00,default=1,qty=2
```

---

## গুরুত্বপূর্ণ টিপস

- **CSV এনকোডিং:** ফাইল অবশ্যই UTF-8 হতে হবে, নাহলে বাংলা/অন্য ভাষার লেখা ভাঙা দেখাবে
- **কমা যুক্ত টেক্সট:** কোনো ভ্যালুতে কমা `,` থাকলে সেটা ডাবল-কোটে রাখুন — `"Men, Women"`
- **প্রথমে ছোট ফাইল দিয়ে টেস্ট করুন:** ৩-৫টা প্রোডাক্ট দিয়ে আগে একবার ইম্পোর্ট করে দেখুন সব ঠিকমতো
  আসছে কিনা, তারপর বড় ফাইল আপলোড করুন
- **Validate Data অবশ্যই আগে চালান:** Import বাটনে ক্লিক করার আগে Validate করলে এরর থাকলে
  ইম্পোর্ট হওয়ার আগেই ধরা পড়বে
- **ছবি ছাড়া টেস্ট করতে চাইলে** `images` কলাম শুধু ফাঁকা রেখে দিন — প্রোডাক্ট তৈরি হয়ে যাবে,
  পরে অ্যাডমিন থেকে ম্যানুয়ালি ছবি যোগ করতে পারবেন
