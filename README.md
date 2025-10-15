Neuralink (PHP API) — how to run locally on Windows

This repository is a small PHP API that uses MySQL. These instructions assume you run on Windows and will use XAMPP (recommended). If you prefer another stack (WAMP, MAMP, Docker), the steps are similar.

Quick overview of what I created for you:
- `db_schema.sql` — SQL file to create the `neuralinkproducts` database and the `category` and `product` tables.

Steps to run locally (PowerShell commands included):

1) Install XAMPP
- Download and install XAMPP: https://www.apachefriends.org/index.html
- Start the XAMPP Control Panel and start Apache and MySQL.

2) Copy project into Apache webroot
Open PowerShell as Administrator and run (adjust source/destination paths if needed):

```powershell
# Replace the source path if your workspace is elsewhere
$src = "D:\NEW PROJECT\neuralink(API)\neuralink\*"
$dest = "C:\xampp\htdocs\neuralink"
New-Item -ItemType Directory -Force -Path $dest
Copy-Item -Path $src -Destination $dest -Recurse -Force
```

3) Import the database schema
If you installed XAMPP to `C:\xampp`, run:

```powershell
# Import the provided SQL (will create database and tables)
& "C:\xampp\mysql\bin\mysql.exe" -u root < "C:\xampp\htdocs\neuralink\db_schema.sql"
```

If your MySQL root has a password, add `-p` and you'll be prompted (or use `-pYourPassword` but avoid saving passwords in scripts).

4) Update database credentials (if needed)
Open `config.php` in the project folder and set the MySQL credentials. Default `config.php` expects root with empty password and DB `neuralinkproducts`:

```php
$host = "localhost";
$user = "root"; // change if needed
$pass = "";     // change if needed
$db   = "neuralinkproducts"; // your database name
```

5) Test the API endpoints
Open a browser or use PowerShell to call endpoints.

Example: list categories (GET)
```powershell
Invoke-RestMethod -Uri 'http://localhost/neuralink/get_categories.php' -Method Get | ConvertTo-Json
```

Example: list products (optionally filter by cat_id)
```powershell
Invoke-RestMethod -Uri 'http://localhost/neuralink/get_products.php?cat_id=1' -Method Get | ConvertTo-Json
```

Example: get product full (POST)
```powershell
Invoke-RestMethod -Uri 'http://localhost/neuralink/get_product_full.php' -Method Post -Body @{ id = 1 }
```

Example: add category (with file) using curl.exe
```powershell
curl.exe -X POST -F "name=My Category" -F "description=Some description" -F "photo=@C:\path\to\image.jpg" "http://localhost/neuralink/add_category.php"
```

Example: add product (with single and multiple images) using curl.exe
```powershell
curl.exe -X POST \
  -F "cat_id=1" \
  -F "name=Test Product" \
  -F "short_description=Short desc" \
  -F "description=Long description here" \
  -F "product_photo=@C:\path\to\img.jpg" \
  -F "multi_photos[]=@C:\path\to\img1.jpg" \
  -F "multi_photos[]=@C:\path\to\img2.jpg" \
  "http://localhost/neuralink/add_product.php"
```

Notes & troubleshooting
- The code treats `isActive = 0` as active (that's how the existing queries work). Do not change the default values unless you update queries.
- Uploads are stored in the `uploads/` folder. Ensure PHP has write permissions to that folder.
- If the `mysql.exe` command is not in PATH, use the full XAMPP path shown above.
- If you prefer not to install XAMPP, you can use Docker or WSL with Apache/PHP and MySQL; tell me your preferred method and I can provide exact commands.

If you'd like, I can also:
- Create a small Postman collection for testing endpoints.
- Add a simple HTML admin page to upload categories/products from the browser.
- Prepare a Docker Compose file for one-command startup.

Tell me which of the above you'd like me to do next.