# ERD & Relasi Eloquent

## ERD

```mermaid
erDiagram
    USERS ||--o{ PURCHASES : "membuat"
    USERS ||--o{ SALES : "membuat"
    SUPPLIERS ||--o{ PURCHASES : "memasok"
    CATEGORIES ||--o{ PRODUCTS : "mengelompokkan"
    PURCHASES ||--|{ PURCHASE_ITEMS : "memiliki"
    SALES ||--|{ SALE_ITEMS : "memiliki"
    PRODUCTS ||--o{ PURCHASE_ITEMS : "dibeli pada"
    PRODUCTS ||--o{ SALE_ITEMS : "dijual pada"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        string role
    }
    CATEGORIES {
        bigint id PK
        string name UK
    }
    SUPPLIERS {
        bigint id PK
        string name
        string phone
        string email
    }
    PRODUCTS {
        bigint id PK
        bigint category_id FK
        string sku UK
        string name
        decimal purchase_price
        decimal selling_price
        int stock
        int minimum_stock
        timestamp deleted_at
    }
    PURCHASES {
        bigint id PK
        bigint supplier_id FK
        bigint user_id FK
        string purchase_number UK
        decimal total
        string status
        datetime purchased_at
    }
    PURCHASE_ITEMS {
        bigint id PK
        bigint purchase_id FK
        bigint product_id FK
        int quantity
        decimal unit_cost
        decimal subtotal
    }
    SALES {
        bigint id PK
        bigint user_id FK
        string sale_number UK
        decimal total
        string status
        datetime sold_at
    }
    SALE_ITEMS {
        bigint id PK
        bigint sale_id FK
        bigint product_id FK
        int quantity
        decimal unit_price
        decimal subtotal
    }
```

## Relasi Eloquent

| Model | Relasi |
|---|---|
| Category | `hasMany(Product)` |
| Product | `belongsTo(Category)`, `hasMany(SaleItem)`, `hasMany(PurchaseItem)` |
| Supplier | `hasMany(Purchase)` |
| Purchase | `belongsTo(Supplier)`, `belongsTo(User)`, `hasMany(PurchaseItem)` |
| PurchaseItem | `belongsTo(Purchase)`, `belongsTo(Product)` |
| User | `hasMany(Sale)`, `hasMany(Purchase)` |
| Sale | `belongsTo(User)`, `hasMany(SaleItem)` |
| SaleItem | `belongsTo(Sale)`, `belongsTo(Product)` |

### hasMany vs belongsTo

- `hasMany` dipakai di sisi **induk** ("satu Sale punya banyak SaleItem").
- `belongsTo` dipakai di sisi **anak** yang menyimpan foreign key ("SaleItem milik satu Sale").
- Foreign key (`sale_id`) berada di tabel **detail** karena satu baris induk dapat memiliki banyak baris anak;
  kolom FK pada tabel anak menunjuk ke satu induk. Kebalikannya (menyimpan banyak id di tabel induk) melanggar
  normalisasi.

### Integritas data (foreign key)

- `products.category_id` → `restrictOnDelete`: kategori yang masih punya produk tidak bisa dihapus.
- `purchase_items.product_id`, `sale_items.product_id` → `restrictOnDelete`: produk bertransaksi tidak bisa di-hard-delete.
- `purchase_items.purchase_id`, `sale_items.sale_id` → `cascadeOnDelete`: detail ikut terhapus bila header dihapus.
- Index tambahan: `products.name`, `sales.sold_at`, `sales.status`, `purchases.purchased_at`, `purchases.status`, `users.role`.
