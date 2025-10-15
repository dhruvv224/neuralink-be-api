const mongoose = require('mongoose');

const ProductSchema = new mongoose.Schema({
  cat_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Category', required: true },
  name: { type: String, required: true },
  short_description: { type: String },
  description: { type: String },
  product_photo: { type: String },
  multi_photos: { type: [String], default: [] },
  isActive: { type: Number, default: 0 }, // 0 = active
  created_at: { type: Date, default: Date.now }
});

module.exports = mongoose.model('Product', ProductSchema);
