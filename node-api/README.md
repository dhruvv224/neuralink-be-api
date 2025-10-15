Node API (Express + MongoDB)

This folder contains a Node.js + MongoDB version of the PHP API.
It mirrors the original endpoints:
- GET  /categories               -> list categories (isActive = 0)
- POST /categories               -> add category (field: name, description, file: photo)
- GET  /products?cat_id=         -> list products (isActive = 0)
- POST /products                 -> add product (fields: cat_id, name, short_description, description; files: product_photo, multi_photos[])
- POST /products/full            -> get full product by id (body: { id })

Quick start (Windows PowerShell)

1) Install Node.js (v18+) and Docker (optional for MongoDB).

2) Open PowerShell in this folder (`node-api`) and install dependencies:

```powershell
npm install
```

3) Start MongoDB
- Option A: Using Docker (recommended):

```powershell
docker-compose up -d
```

- Option B: Run a local MongoDB instance and set `MONGO_URI` in `.env` accordingly.

4) Copy `.env.example` to `.env` and adjust if needed:

```powershell
copy .env.example .env
```

5) Start the server:

```powershell
npm run dev
```

6) Seed sample data (optional):

```powershell
npm run seed
```

7) Test endpoints (PowerShell examples):

List categories:
```powershell
Invoke-RestMethod -Uri 'http://localhost:5000/categories' -Method Get | ConvertTo-Json
```

List products for category 1 (replace with real id):
```powershell
Invoke-RestMethod -Uri 'http://localhost:5000/products?cat_id=<catId>' -Method Get | ConvertTo-Json
```

Get full product by id:
```powershell
Invoke-RestMethod -Uri 'http://localhost:5000/products/full' -Method Post -Body @{ id = '<productId>' }
```

Add category with curl (PowerShell):
```powershell
curl.exe -X POST -F "name=My Category" -F "description=Some description" -F "photo=@C:\path\to\image.jpg" "http://localhost:5000/categories"
```

Add product with curl (PowerShell):
```powershell
curl.exe -X POST -F "cat_id=<catId>" -F "name=Test Product" -F "short_description=Short desc" -F "description=Long description" -F "product_photo=@C:\path\to\img.jpg" -F "multi_photos[]=@C:\path\to\img1.jpg" -F "multi_photos[]=@C:\path\to\img2.jpg" "http://localhost:5000/products"
```

Notes
- Uploads are saved to the `uploads/` folder and served at `http://localhost:5000/uploads/...`.
- The model keeps `isActive` as a number with `0` meaning active to match the original PHP behavior.
- If you want, I can also add endpoints for update/delete and a small HTML admin UI.
