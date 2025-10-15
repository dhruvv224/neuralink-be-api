const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const Product = require('../models/Product');

const uploadDir = path.join(__dirname, '..', process.env.UPLOAD_DIR || 'uploads');
const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, uploadDir),
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname);
    const name = 'product_' + Date.now() + '_' + Math.random().toString(36).slice(2, 9) + ext;
    cb(null, name);
  }
});
const upload = multer({ storage });

// GET /products?cat_id= - list products
router.get('/', async (req, res) => {
  try {
    const { cat_id } = req.query;
    const filter = { isActive: 0 };
    if (cat_id) filter.cat_id = cat_id;
    const prods = await Product.find(filter).select('id cat_id name short_description product_photo description');
    res.json({ success: true, data: prods });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

// POST /products - add product (single product_photo and multiple multi_photos[])
const cpUpload = upload.fields([ { name: 'product_photo', maxCount: 1 }, { name: 'multi_photos', maxCount: 10 } ]);
router.post('/', cpUpload, async (req, res) => {
  try {
    const { cat_id, name, short_description, description } = req.body;
    if (!cat_id || !name || !short_description || !description) {
      return res.json({ success: false, message: 'All fields are required.' });
    }

    const host = process.env.HOST || `${req.protocol}://${req.get('host')}`;
    let productPhotoUrl = '';
    if (req.files && req.files['product_photo'] && req.files['product_photo'][0]) {
      productPhotoUrl = `${host}/uploads/${req.files['product_photo'][0].filename}`;
    }

    let multiPhotos = [];
    if (req.files && req.files['multi_photos']) {
      multiPhotos = req.files['multi_photos'].map(f => `${host}/uploads/${f.filename}`);
    }

    const prod = new Product({ cat_id, name, short_description, description, product_photo: productPhotoUrl, multi_photos: multiPhotos, isActive: 0 });
    await prod.save();
    res.json({ success: true, message: 'Product added successfully.' });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

// POST /products/full - get product by id (mirrors get_product_full.php)
router.post('/full', async (req, res) => {
  try {
    const { id } = req.body;
    if (!id) return res.json({ success: false, message: 'Product ID is required' });
    const prod = await Product.findById(id);
    if (!prod) return res.json({ success: false, message: 'Product not found' });
    res.json({ success: true, data: prod });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

// POST /products/update - update product (handles product_photo replacement, multi_photos add/remove)
const cpUploadUpdate = upload.fields([ { name: 'product_photo', maxCount: 1 }, { name: 'multi_photos', maxCount: 10 } ]);
router.post('/update', cpUploadUpdate, async (req, res) => {
  try {
    const { id, cat_id, name, short_description, description, isActive, deleted_photos } = req.body;
    if (!id) return res.json({ success: false, message: 'Product ID is required' });
    const prod = await Product.findById(id);
    if (!prod) return res.json({ success: false, message: 'Product not found' });

    // Validate cat_id if provided (keep old if invalid)
    if (cat_id) prod.cat_id = cat_id;

    if (typeof name !== 'undefined') prod.name = name;
    if (typeof short_description !== 'undefined') prod.short_description = short_description;
    if (typeof description !== 'undefined') prod.description = description;
    if (typeof isActive !== 'undefined') prod.isActive = Number(isActive);

    const host = process.env.HOST || `${req.protocol}://${req.get('host')}`;

    // Handle single product photo replacement
    if (req.files && req.files['product_photo'] && req.files['product_photo'][0]) {
      // delete old file
      if (prod.product_photo) {
        try {
          const filename = prod.product_photo.split('/uploads/').pop();
          const fs = require('fs');
          const p = path.join(uploadDir, filename);
          if (fs.existsSync(p)) fs.unlinkSync(p);
        } catch (e) {}
      }
      prod.product_photo = `${host}/uploads/${req.files['product_photo'][0].filename}`;
    }

    // Handle multi_photos deletions
    let existing = Array.isArray(prod.multi_photos) ? prod.multi_photos.slice() : [];
    if (deleted_photos) {
      const dels = deleted_photos.split(',').map(s => s.trim()).filter(Boolean);
      const fs = require('fs');
      dels.forEach(url => {
        // remove file
        try {
          const filename = url.split('/uploads/').pop();
          const p = path.join(uploadDir, filename);
          if (fs.existsSync(p)) fs.unlinkSync(p);
        } catch (e) {}
      });
      existing = existing.filter(u => !dels.includes(u));
    }

    // Add new multi_photos if uploaded
    if (req.files && req.files['multi_photos']) {
      const added = req.files['multi_photos'].map(f => `${host}/uploads/${f.filename}`);
      existing = existing.concat(added);
    }
    prod.multi_photos = existing;

    await prod.save();
    res.json({ success: true, message: 'Product updated successfully' });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

// POST /products/delete - soft delete
router.post('/delete', async (req, res) => {
  try {
    const { id } = req.body;
    if (!id) return res.json({ success: false, message: 'ID required' });
    const prod = await Product.findById(id);
    if (!prod) return res.json({ success: false, message: 'Product not found' });
    prod.isActive = 1;
    await prod.save();
    res.json({ success: true, message: 'Product deleted' });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

module.exports = router;
