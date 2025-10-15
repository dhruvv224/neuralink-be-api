const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const Category = require('../models/Category');

const uploadDir = path.join(__dirname, '..', process.env.UPLOAD_DIR || 'uploads');
const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, uploadDir),
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname);
    const name = 'cat_' + Date.now() + '_' + Math.random().toString(36).slice(2, 9) + ext;
    cb(null, name);
  }
});
const upload = multer({ storage });

// GET /categories - list active categories
router.get('/', async (req, res) => {
  try {
    const cats = await Category.find({ isActive: 0 }).sort({ created_at: -1 });
    res.json({ success: true, data: cats });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

// POST /categories - add category (with optional photo)
router.post('/', upload.single('photo'), async (req, res) => {
  try {
    const { name, description } = req.body;
    if (!name || !description) {
      return res.json({ success: false, message: 'Name and description are required.' });
    }

    let photoUrl = '';
    if (req.file) {
      const host = process.env.HOST || `${req.protocol}://${req.get('host')}`;
      photoUrl = `${host}/uploads/${req.file.filename}`;
    }

    const cat = new Category({ name, description, photo: photoUrl, isActive: 0 });
    await cat.save();
    res.json({ success: true, message: 'Category added successfully.', id: cat._id });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

// POST /categories/update - update category (id, name, description, isActive, optional photo)
router.post('/update', upload.single('photo'), async (req, res) => {
  try {
    const { id, name, description, isActive } = req.body;
    if (!id) return res.json({ success: false, message: 'ID is required' });

    const cat = await Category.findById(id);
    if (!cat) return res.json({ success: false, message: 'Category not found' });

    // Replace photo if uploaded
    if (req.file) {
      // remove old file if exists
      if (cat.photo) {
        try {
          const filename = cat.photo.split('/uploads/').pop();
          const fs = require('fs');
          const p = path.join(uploadDir, filename);
          if (fs.existsSync(p)) fs.unlinkSync(p);
        } catch (e) {}
      }
      const host = process.env.HOST || `${req.protocol}://${req.get('host')}`;
      cat.photo = `${host}/uploads/${req.file.filename}`;
    }

    if (typeof name !== 'undefined') cat.name = name;
    if (typeof description !== 'undefined') cat.description = description;
    if (typeof isActive !== 'undefined') cat.isActive = Number(isActive);

    await cat.save();
    res.json({ success: true, message: 'Category updated' });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

// POST /categories/delete - soft delete (set isActive=1)
router.post('/delete', async (req, res) => {
  try {
    const { id } = req.body;
    if (!id) return res.json({ success: false, message: 'ID is required' });
    const cat = await Category.findById(id);
    if (!cat) return res.json({ success: false, message: 'Category not found' });
    cat.isActive = 1;
    await cat.save();
    res.json({ success: true, message: 'Category deleted' });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
});

module.exports = router;
